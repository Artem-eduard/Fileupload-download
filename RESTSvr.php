<?php

$output_file =$_POST['output_file'];   //get the parameter output_file from POST BODY
define("TIMEOUT", 1 );                  //set TIMEOUT
$NUM_OF_ATTEMPTS = 2;
$attempts = 0;
$error_flag = 0;
if ($_FILES) {  //check if there is file uploaded
    if ($_FILES['input_file']['tmp_name'])  //if there is input_file attached from request in the temporary uploaded file
    {
        $uploaddir = "/tmp/";
        $uploadfile = parse_url($uploaddir . basename($_FILES['input_file']['name']), PHP_URL_PATH); //real input_file path should be stored in server
        $uploadfile = $_SERVER['DOCUMENT_ROOT'] . parse_url($uploadfile, PHP_URL_PATH);

        if (move_uploaded_file($_FILES['input_file']['tmp_name'], $uploadfile)) //rename it to original name in tmp
        {
            echo ("Upload successfully");
        }
        else
        {
            $error_flag = 1;
            echo ("File was not found");
        }
    }
}
else {
    $error_flag = 1;
}

#if ($error_flag == 1)   //if A part has failed then B part
{
    $outputdir = "/RESTServer/output/"; //where output_file have to move
    $outputfile = $outputdir . $output_file;
    $outputfile_end = $outputfile . ".end"; // make .end file

    do {
        try {

		var_dump(parse_url($outputfile_end,PHP_URL_PATH));
            if (file_exists(parse_url($outputfile_end, PHP_URL_PATH))) {
                $file_url = parse_url($outputfile, PHP_URL_PATH);
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($file_url));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_url));
                ob_clean();
                flush();

                readfile($file_url);        //return file to the user
                exit;
                $error_flag = 0;        //no need to go to C
	    
	    }
	    else
	    {
		echo("file not found");
	    }

        }catch (Exception $e){
            $attempts++;
            sleep(TIMEOUT * 1000);  //waiting for TIMEOUT second. used to wait for the file to return to the user.
            continue;
        }
        break;
    }
    while($attempts < $NUM_OF_ATTEMPTS); //if retry count is over NUM_OF_ATTEMPTS, break loop;
}

$attempts = 0;
if ($error_flag == 1)           //if there is no success in B part
{
    $inputdir = "/RESTServer/output/";
    $inputtextfile = $inputdir . $_POST['input_text'];
    $inputtextfile_end = $inputtextfile . ".end";

    do{
        try{
            if (file_exists(parse_url($inputtextfile_end, PHP_URL_PATH))) {
                $file_url =parse_url($inputtextfile, PHP_URL_PATH);
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($file_url));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_url));
                ob_clean();
                flush();
		read_file($file_url);
		exit;

            }
            else
            {

            }

        }catch (Exception $e)
        {
            $attempts++;
            sleep(TIMEOUT * 1000);  //waiting for TIMEOUT second. used to wait for the file to return to the user.
            continue;
        }
        break;
    }
    while($attempts < $NUM_OF_ATTEMPTS); //if retry count is over NUM_OF_ATTEMPTS, break loop;
}

?>
