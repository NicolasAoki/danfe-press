<?php

$inoInst = inotify_init();

stream_set_blocking($inoInst, 0);

// not the best way but sufficient for this example :-)
$dirWatch = '/sircplus/dados/csag/nfce/f0100/ret';

$watch_id = inotify_add_watch($inoInst, $dirWatch, IN_ALL_EVENTS);
while (true)
{
    // read events
    $events = inotify_read($inoInst);

        if ($events[0]['mask'] === IN_CREATE)
        {
            printf("Created file: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_DELETE)
        {
            printf("Deleted file: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_ACCESS)
        {
            printf("Accessed file: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_MODIFY)
        {
            printf("Modified file: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_ATTRIB)
        {
            printf("Metadata changed. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_CLOSE_WRITE)
        {
            printf("File opened for writing was closed. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_CLOSE_NOWRITE)
        {
            printf("File not opened for writing was closed. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_OPEN)
        {
            printf("File opened. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_MOVED_TO)
        {
            printf("File moved into directory. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_MOVED_FROM)
        {
            printf("File moved out of directory. file: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_DELETE_SELF)
        {
            printf("Watched directory deleted. file: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_MOVE_SELF)
        {
            printf("Watched directory moved. file: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_CLOSE)
        {
            printf("File closed. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_MOVE)
        {
            printf("File moved. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_ALL_EVENTS)
        {
            printf("IN_ALL_EVENTS activated. File: %s in watch_dir\n", $events[0]['name']);
        }

        if ($events[0]['mask'] === IN_UNMOUNT)
        {
            printf("File system containing watched object was unmounted. File: %s in watch_dir\n", $events[0]['name']);
        }


    

}


inotify_rm_watch($inoInst, $watch_id);
// close our inotify instance

fclose($inoInst);

?>