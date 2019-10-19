<?php

class SendflowPublic
{
    const OPTION_NAME = 'sendflow_data';

    public static function init()
    {
        add_action('wp_head', array(__CLASS__, 'sendflow_header'), 10);
    }

    public static function sendflow_header()
    {
        $data = self::getData();

        if (empty($data['api_key']) || empty($data['website_uid'])) {
            return false;
        }

        $fullSdkDirectoryPath = plugin_dir_url( __FILE__ );

        echo "<script>
var wp_sendflow_worker_directory_path = '" . $fullSdkDirectoryPath . "/sdk/sendflow-worker.js.php';
!function(e,n,s,t,o){e.sendflow=e.sendflow||function(){(e.sendflow.q=e.sendflow.q||[]).push(arguments)},
t=n.createElement('script'),o=n.getElementsByTagName('script')[0],
t.async=1,t.src='//cdn.sendflow.pl/" . $data['website_uid'] . "/library-main.min.js',
o.parentNode.insertBefore(t,o)}(window,document);
</script>";
    }

    private static function getData()
    {
        return get_option(self::OPTION_NAME, array());
    }
}

?>
