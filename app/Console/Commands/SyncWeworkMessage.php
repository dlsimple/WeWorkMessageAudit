<?php

namespace App\Console\Commands;

use App\Models\WxworkMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncWeworkMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xlogical:sync-wework-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'call php7-wxwork-finance to sync wework message to database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $corp_id = config('messageaudit.corp_id');
        $secret = config('messageaudit.secret');
        $privateKey = config('messageaudit.private_key');
        $limit = config('messageaudit.data_limit');
        $seq = WxworkMessage::max('seq');

        try {
            $wxworkFinance = new \WxworkFinanceSdk($corp_id, $secret);
            $chats = json_decode($wxworkFinance->getChatData($seq, $limit), true);

            if($chats['errcode'] != 0) {
                Log::info($chats['errmsg'], ['error:' . $chats['errcode']]);
                return;
            }

            foreach ($chats['chatdata'] as $encrypt_message) {
                
                $decryptRandKey = null;
                openssl_private_decrypt(base64_decode($encrypt_message['encrypt_random_key']), $decryptRandKey, $privateKey, OPENSSL_PKCS1_PADDING);

                $decrypt_message = json_decode($wxworkFinance->decryptData($decryptRandKey, $encrypt_message['encrypt_chat_msg']), true);

                if(in_array($decrypt_message['action'], ['recall', 'switch'])) {
                    Log::info($decrypt_message, ['todo']);
                    continue;
                }

                if(in_array($decrypt_message['msgtype'], ['location','emotion', 'image', 'file', 'disagree', 'voiptext', 'weapp', 'video', 'mixed'])){
                    Log::info($decrypt_message, ['todo']);
                    continue;
                }

                if($decrypt_message['msgtype'] == 'text') {
                    $content = $decrypt_message['text']['content'];
                }elseif($decrypt_message['msgtype'] == 'markdown') {
                    $content = $decrypt_message['info']['content'];
                }elseif($decrypt_message['msgtype'] == 'chatrecord') {
                    $content = json_encode($decrypt_message['chatrecord']['item']);
                }elseif($decrypt_message['msgtype'] == 'link') {
                    $content = json_encode($decrypt_message['link']);
                }else {
                    Log::info($decrypt_message, ['todo']);
                    continue;
                }

                WxworkMessage::updateOrCreate(
                    ['msgid' => $encrypt_message['msgid']],
                    [
                    'seq' => $encrypt_message['seq'],
                    'msgid' => $encrypt_message['msgid'],
                    'action' => $decrypt_message['action'],
                    'from' => $decrypt_message['from'],
                    'roomid' => $decrypt_message['roomid'],
                    'tolist' => json_encode($decrypt_message['tolist']),
                    'msgtime' => $decrypt_message['msgtime'],
                    'msgtype' => $decrypt_message['msgtype'],
                    'msgcontent' => $content,
                    'encrypt_content' => $encrypt_message,
                ]
                );
            }

        }catch(\WxworkFinanceSdkException $e) {
            Log::info($e->getMessage(), ['error:' . $e->getCode()]);
        }

        return 0;
    }
}
