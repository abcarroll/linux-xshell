<?php
    /*
     * Copyright (c) 2015, A.B. Carroll, ben@hl9.net
     * All rights reserved.  Available from http://github.com/nezzario/linux-xshell
     *
     * Licensed under the BSD license.  See LICENSE file for full license.
     */

    $xshell_dir = "xshell";;

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($xshell_dir, RecursiveDirectoryIterator::SKIP_DOTS));

    $connections = [];

    /** @var SplFileInfo $file */
    foreach ($files as $file) {
        if(strtolower($file->getExtension()) != 'xsh') {
            continue;
        }

        $data = [];
        $lines = file($file->getPathname());
        $section = '';
        foreach ($lines as $line) {
            $line = rtrim($line);
            if(substr($line, 0, 1) == '[' && substr($line, -1) == ']') {
                $section = substr($line, 1, -1) . '_';
                $section = str_replace(':', '_', $section); // Not sure why the .xsh files are so messy like this
            } else {
                list($key, $value) = explode('=', $line, 2);
                $data[strtolower($section . $key)] = $value;
            }
        }

        if(isset($data['connection_protocol']) && strtolower($data['connection_protocol']) == 'ssh') {
            $relative_path = substr($file->getPathname(), (strlen($xshell_dir) + 1));
            $short_name = substr($relative_path, 0, (-1 * strlen($file->getExtension()) - 1));
            $connections[$short_name] = [
                'protocol' => strtolower($data['connection_protocol']),
                'host' => $data['connection_host'],
                'port' =>  $data['connection_port'],
                'user' => $data['connection_authentication_username'],
                'key' => $data['connection_authentication_userkey'],

                'path' => $file->getPathname(),
                'relative_path' => $relative_path,
            ];

        }

    }

    $screenWidth = exec('tput cols');

    $printCells = [];
    $maxIdLength = strlen(count($connections));
    $maxCellLength = 0;

    $id = 0;
    foreach($connections as $key => $data) {
        $cell = str_pad($id++, $maxIdLength + 1, ' ', STR_PAD_LEFT) . ' ' . $key;
        $printCells[] = $cell;

        if(strlen($cell) > $maxCellLength) {
            $maxCellLength = strlen($cell);
        }
    }

    $maxCellLength += 2;
    $numCols = floor($screenWidth / $maxCellLength);

    $screenLeft = $screenWidth;
    //$i = 0;
    //for($x = 0; $x < count($printCells); $x++) {
    foreach($printCells as $cell) {
        //$cell = $printCells[$z++];
        if($screenLeft - $maxCellLength < 0) {
            echo "\n";
            $screenLeft = $screenWidth;
        }

        echo str_pad($cell, $maxCellLength, ' ', STR_PAD_RIGHT) . '|';
        $screenLeft -= $maxCellLength;
    }

    echo "\n";

    echo ' > ';
    #$value = fgets(STDIN);

    echo "\n\n";
