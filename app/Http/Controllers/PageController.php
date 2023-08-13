<?php

namespace App\Http\Controllers;

use Barryvdh\Debugbar\Facades\Debugbar;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use function request;

class PageController extends Controller {

    public function getName() {
        $url = request('url');

        $process = new Process(array('yt-dlp',
            '--print',
            '%(title)s',
            $url
        ));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $newString = mb_convert_encoding([
            'text' => $process->getOutput(),
        ], "UTF-8", "auto");
        return response()->json($newString);
    }

    public function deleteFiles() {
        array_map("unlink", glob(public_path('/output/*.*')));
    }

    public function getFile() {
        $url = request('url');
        $this->deleteFiles();

//        $process = new Process(array('yt-dlp',
//            '--output',
//            'output/%(title)s.%(ext)s',
//            '--write-thumbnail',
//            '--extract-audio',
//            '--audio-format', 'mp3',
//            $url
//        ));

        //yt-dlp -f ba -x --audio-format mp3 --downloader=aria2c --downloader--args '--min-split-size=1M --max-connection-per-server=16 --max-concurrent-downloads=16 --split=16' $URL_HERE
        // https://youtu.be/9JPPcXkoZfo

        $process = new Process(array('yt-dlp',
            '-x',
            '--audio-format',
            'mp3',
            '--audio-quality',
            '5',
            '--output',
            'output/%(title)s.%(ext)s',
//            '--write-thumbnail',
//            '--downloader=aria2c',
//            '--downloader-args',
//            '\'--min-split-size=1M --max-connection-per-server=16 --max-concurrent-downloads=16 --split=16\'',
            $url
        ));
        $process->setTimeout(3600);
        $process->run();

        if (!$process->isSuccessful()) {
            Debugbar::log($process->getErrorOutput());
            throw new ProcessFailedException($process);
        }

        $name = null;
        $file = null;
        $thumb = null;
        $ext = '';
        $files = scandir(public_path('output'));
        foreach ($files as $file_name) {
            if ($file_name != '.' || $file_name != '..') {
                if (preg_match('/\.(mp3)/', $file_name)) {
                    $new_filename = preg_replace("/[^()-+?. a-zа-яё\d]/ui", "", $file_name);
                    rename(public_path('output/') . $file_name,
                             public_path('output/') . $new_filename);
                    $file = 'output/' . $new_filename;
                    $name = preg_replace('/\.(mp3)/', '', $new_filename);
                }
                if (preg_match('/\.(jpg|jpeg|webp|png)/', $file_name)) {
                    $new_filename = preg_replace("/[^()-+?. a-zа-яё\d]/ui", "", $file_name);
                    rename(public_path('output/') . $file_name,
                             public_path('output/') . $new_filename);
                    $dot_index = mb_strrpos($new_filename, '.');
                    $ext = mb_substr($new_filename, $dot_index);
                    $thumb = 'output/' . $name . $ext;
                }
            }
        }
        $webp = $ext == '.webp';
        $newString = mb_convert_encoding([
            'success' => true,
            'file' => $file,
            'name' => $name,
            'thumb' => $thumb,
            'webp' => $webp
        ], "UTF-8", "auto");
        return response()->json($newString);
    }

    public function showGoogleApi() {
        return view('pages.google-api-view');
    }

    public function showTest2() {
        return view('pages.test2');
    }

    public function startApi() {
        $urls = [
            'http://45.8.96.6/google-api',
            'http://45.8.96.6/test1',
            'http://45.8.96.6/test2'
        ];

        $client = new Google_Client();

        $client->setAuthConfig(public_path('/api/my-project-test1.json'));
        $client->addScope('https://indexing.googleapis.com/batch');
        $client->setUseBatch(true);

        $service = new Google_Service_Indexing($client);
        $batch = $service->createBatch();

        foreach ($urls as $url) {
            $postBody = new Google_Service_Indexing_UrlNotification();
            $postBody->setType('URL_UPDATED');
            $postBody->setUrl($url);
            $batch->add($service->urlNotifications->publish($postBody));
        }
        $results = $batch->execute(); // it does authorize in execute()

        dump($results);

        foreach ($results as $res) {
            dump($res->getErrors());
        }
    }

    public function sendIndexNow() {
        $list_url = [
            'http://test1ng.site/test1',
            'http://test1ng.site/test2',
        ];

        $data = [
            'host' => 'test1ng.site',
            'key' => '4f9527fd1d5843b3b272e0d10184c570',
            "keyLocation" => "http://test1ng.site/4f9527fd1d5843b3b272e0d10184c570.txt",
            'urlList' => $list_url
        ];

        $client = new Client();
        try {
            $client->request('POST', 'https://yandex.com/indexnow', [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Host' => 'yandex.com'
                ],
                'json' => $data]);
            return ['success' => true];
        } catch (GuzzleException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }



//        $dir = public_path('sitemaps/json/');
//        if (!is_dir($dir)) {
//            mkdir($dir, 0777, true);
//        }

//        file_put_contents(public_path('sitemaps/json/') . 'urls' . '.json', json_encode($data));

        $dir = public_path('sitemaps/json/');
        $files = scandir($dir);
        natcasesort($files); //отсортировать файлы по порядку
        $client = new Client();
        $res = [];
        foreach ($files as $file_name) {
            if ($file_name != "." && $file_name != "..") {
                $data = file_get_contents($dir . $file_name);
                if ($data) {
                    $res[] = $file_name;
                    try {
                        $client->request('POST', 'https://yandex.com/indexnow', [
                            'headers' => [
                                'Content-Type' => 'application/json; charset=utf-8',
                                'Host' => 'yandex.com'
                            ],
                            'json' => json_decode($data)]);
                    } catch (GuzzleException $e) {
                        DebugBar::warning($e->getMessage());
                        return ['success' => false, 'file' => $file_name, 'msg' => $e->getMessage()];
                    }
                }
            }
        }

        array_map("unlink", glob(public_path('/sitemaps/json/*.json')));
        return ['success' => true, 'files' => $res];
    }
}
