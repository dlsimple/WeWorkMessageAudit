<?php

namespace App\Console\Commands;

use App\Models\WxworkMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SyncWxworkMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xlogical:sync-wxwork-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'call php7-wxwork-finance to sync wxwork message to database';

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

            if ($chats['errcode'] != 0) {
                Log::info($chats['errmsg'], ['error:'.$chats['errcode']]);

                return;
            }

            foreach ($chats['chatdata'] as $encrypt_message) {

                $decryptRandKey = null;
                openssl_private_decrypt(base64_decode($encrypt_message['encrypt_random_key']), $decryptRandKey, $privateKey, OPENSSL_PKCS1_PADDING);

                $decrypt_message = json_decode($wxworkFinance->decryptData($decryptRandKey, $encrypt_message['encrypt_chat_msg']), true);

                if (in_array($decrypt_message['action'], ['recall', 'switch'])) {
                    continue;
                }

                if ($decrypt_message['msgtype'] == 'text') {
                    $content = $decrypt_message['text']['content'];
                } elseif ($decrypt_message['msgtype'] == 'markdown') {
                    $content = $decrypt_message['info']['content'];
                } else {
                    $msg_type_key = [
                        'image' => 'image',
                        'voice' => 'voice',
                        'video' => 'video',
                        'card' => 'card',
                        'location' => 'location',
                        'emotion' => 'emotion',
                        'file' => 'file',
                        'link' => 'link',
                        'weapp' => 'weapp',
                        'chatrecord' => 'chatrecord',
                        'todo' => 'todo',
                        'vote' => 'vote',
                        'collect' => 'collect',
                        'redpacket' => 'redpacket',
                        'meeting' => 'meeting',
                        'mixed' => 'mixed.item',
                        'docmsg' => 'doc',
                        'news' => 'info.item',
                        'calendar' => 'calendar',
                        'meeting_voice_call' => 'meeting_voice_call',
                        'voip_doc_share' => 'voip_doc_share',
                        'external_redpacket' => 'redpacket',
                        'sphfeed' => 'sphfeed',
                        'voiptext' => 'info',
                        'qydiskfile' => 'info',
                        'agree' => 'agree',
                        'disagree' => 'disagree',
                    ];
                    $content = json_encode(Arr::get($decrypt_message, $msg_type_key[$decrypt_message['msgtype']]));
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

        } catch (\WxworkFinanceSdkException $e) {
            Log::info($e->getMessage(), ['error:'.$e->getCode()]);
        }

        return 0;
    }
}
