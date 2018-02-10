<?php namespace App\Console\Commands;

use App\Http\Logic\PublisherLogic;
use Illuminate\Console\Command;
use Mail;

class CheckPublisherType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'command:check-publisher-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查Publisher表SiteType是否与SiteTypeNew字段保持一致;';

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
     * @return mixed
     */
    public function handle()
    {
        $publishers  = (new PublisherLogic())->getSiteTypeNonePublishers();
        $flag = Mail::send(
            'emails.checkType',
            ['name' => 'System','data' => $publishers],
            function ($message) {
                $to = 'seandiao@brandreward.com';
                $message->to($to)->subject("Publisher SiteType Check");
            }
        );
        if ($flag) {
            echo 'Send success,please check!';
        } else {
            echo 'Send failed,try again!';
        }
    }
}
