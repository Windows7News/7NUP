<?php
define("ROOT", dirname(__FILE__));

include ROOT . "/Qiniu/Client.php";
\Qiniu\Client::registerAutoloader();

$bucket = "mychina";
$QiniuBaseUrl = "mychina.qiniudn.com";
$QiniuAccessKey = 'ulXjp8N1AWdmg4IMgi6j41ifQzubUqdeUWaNCAeV';
$QiniuSecretKey = 'eNAYOZpGiexcVHdTj-GATnGZFXkdkZ846v5xwSwT';

$config = array('access_key' => $QiniuAccessKey,'secret_key' => $QiniuSecretKey);
$sdk = new \Qiniu\Client($config);

ob_start();

$htmlFileTableHead = <<< HTML
<section class='box well'>
  <header><h2>已上传文件列表</h2></header>
  <table class="table table-striped table-bordered table-condensed">
HTML;

$htmlFileTableFooter = <<< HTML
  </table>
</section>
HTML;

switch($_SERVER["REQUEST_METHOD"])
{
    case "POST":
        if(isset($_FILES["files"]))
        {
            echo $htmlFileTableHead;
            foreach($_FILES["files"]["error"] as $key => $error)
            {
                if($error == UPLOAD_ERR_OK)
                {
                    $tmpName = $_FILES["files"]["tmp_name"][$key];
                    $fileName = $_FILES["files"]["name"][$key];
                    $fileMD5 = md5_file($_FILES["files"]["tmp_name"][$key]);

                    $params = array('scope' => $bucket,'expires' => 3600);
                    $body = array('file' => '@' . $tmpName);
                    list($return, $error) = $sdk->putFile($bucket, $fileMD5, $body, $params);
                    if ($error !== null) {
                        $error_arr = json_encode($error);
                        echo "上传发生错误：{$error_arr['error']}，请稍后再试。";
                    } else {
                        $fileSize = ceil($_FILES["files"]["size"][$key] / 1024) . "KB";
                        $fileType = $_FILES["files"]["type"][$key];
                        $fileURL = "http://{$QiniuBaseUrl}/{$fileMD5}";
                        $fileURL = "<a href='{$fileURL}' target='_blank'>{$fileURL}</a>";

                        echo <<< HTML
                  <tr>
                    <td>{$fileName}</td>
                    <td>{$fileType}</td>
                    <td>{$fileSize}</td>
                    <td>{$fileURL}</td>
                  </tr>
HTML;
                    }
                }
            }
            echo $htmlFileTableFooter;
        }
        break;
}

$htmlOut = ob_get_clean();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>7NUP</title>
    <link href="http://cdn.staticfile.org/twitter-bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
</head>
<body style='font-family: "WenQuanYi Micro Hei", "WenQuanYi Zen Hei", "Microsoft YaHei", arial, sans-serif; font-size: 16px;margin: 30px auto;'>
<div class="container">
    <div class="row">
        <div class="span12">
            <header class="box well">
                <header><h2>7N UP</h2></header>
                <hr />
                <ul style="list-style-type: none;">
                    <li><i class="icon-ok"></i> 不必注册帐号，不必登录</li>
                    <li><i class="icon-ok"></i> 直链下载，无广告，无需等待</li>
                    <li><i class="icon-ok"></i> 七牛CDN全网加速，支持<a href="http://developer.qiniu.com/docs/v6/api/reference/fop/">多媒体在线处理</a></li>
                    <li><i class="icon-ok"></i> 清爽无图界面，无须Flash，同样适合手机访问</li>
                </ul>
            </header>
            <?php echo $htmlOut;?>
            <section class='box well'>
                <form id="fileForm" method="post" enctype="multipart/form-data">
                    <input type="file" name="files[]" /><br />
                </form>
                <hr />
                <button id='addFile' class='btn btn-info'><i class="icon-plus icon-white"></i> 添加新的上传框</button>
                <button id='start' class='btn btn-success'><i class="icon-play icon-white"></i> 开始上传</button>
            </section>
            <section class='box well'>
                <ul style="list-style-type:none;">
                    <li>作者 <a href="http://faceair.net/" target="_brank">faceair</a></li>
                    <li>源代码托管于 <a href="https://github.com/faceair/7NUP" target="_brank">Github/7NUP</a> GPLv3</li>
                </ul>
            </section>
        </div>
    </div>
</div>
<script type='text/javascript' src='http://cdn.staticfile.org/jquery/2.0.3/jquery.min.js'></script>
<script type='text/javascript' src='http://cdn.staticfile.org/twitter-bootstrap/3.0.3/js/bootstrap.min.js'></script>
<script type="text/javascript">
    $("#addFile").click(function(){
        $("#fileForm").append('<input type="file" name="files[]" /><br />');
    });
    $("#start").click(function(){
        $("#fileForm").submit();
    });
</script>
</body>
</html>
