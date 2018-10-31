<?php
ini_set('display_errors', 'On');
include_once 'bootstrap.php';

use NFePHP\Extras\Danfe;
use NFePHP\Common\Files\FilesFolders;

foreach(glob('teste_xml/*xml') as $filename){

    //$xml = basename($filename);
    
    $docxml = FilesFolders::readFile($filename);
    
    $danfe = new Danfe($docxml, 'P', 'A4', '', 'I', '');
    $id = $danfe->montaDANFE();
    
    $teste = $danfe->printDANFE($id.'.pdf', 'F');
    

}

$dirWatch = 'teste_xml';

// Open an inotify instance
$inoInst = inotify_init();

// this is needed so inotify_read while operate in non blocking mode
stream_set_blocking($inoInst, 0);

// watch if a file is created or deleted in our directory to watch
$watch_id = inotify_add_watch($inoInst, $dirWatch, IN_ALL_EVENTS);

// not the best way but sufficient for this example :-)
while(true){
    
    // read events (
    // which is non blocking because of our use of stream_set_blocking
    $events = inotify_read($inoInst);
    
    // output data
    print_r($events);
}

// stop watching our directory
inotify_rm_watch($inoInst, $watch_id);

// close our inotify instance
fclose($inoInst);
/*
$xml = '11101284613439000180550010000004881093997017-nfe.xml';

$docxml = FilesFolders::readFile($xml);

$danfe = new Danfe($docxml, 'P', 'A4', '', 'I', '');
$id = $danfe->montaDANFE();

$teste = $danfe->printDANFE($id.'.pdf', 'F');
*/
?> 

<html>
<body>
  <div>
  asdasdasd
  <!-- 
    //<object data="11101284613439000180550010000004881093997017-nfe.pdf" type="application/pdf" width="800" height="400">
    //alt : <a href="11101284613439000180550010000004881093997017-nfe.pdf">test.pdf</a>
    //</object>
     -->
  </div>
</body>
</html>
