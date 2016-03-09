UPLOAD ARCHIVES CLASS
===============
#### Class PHP to upload files to a server through pre-established parameters.

##### Set of classes to upload any files via PHP, working with compression, decompression, image format base64, processing and cutting images, check sizes H x W image and etc. Class to delete the file from the server through the necessary parameters.

## Params
- 1 => realName -> Filename received via $ _FILES[] or any other method. It is a mandatory variable.
- 2 => sizeArchive -> File size received via $ _FILES [] or any other method. It is a mandatory variable.
- 3 => tempPath -> Temporary folder where the file was sent, received via $ _FILES [] or any other method. It is a mandatory variable.
- 4 => newPath -> Chosen destination for the file to be sent. It is a mandatory variable.
- 5 => personalFolder -> If sent, this parameter is added to the file destination path and creates the folder if necessary. Example: newpath . '/ New /' ; The 'new' folder will be created if it does not exist on the target. There is a mandatory variable.
- 6 => arrayExtensions -> Send array of allowable formats in instantiated class, if you send an empty array '[]' will receive the standard class that is working with the types ['doc', 'docx', 'pdf', 'xls', 'xlsx' 'jpg', 'png', 'gif', 'zip', 'DOC', 'DOCX', 'PDF', 'XLS', 'XLSX', 'JPG', 'PNG', 'GIF', 'ZIP']. There is a mandatory variable.
- 7 => limitSizeUpload -> Maximum file size without going empty 10mb will default. There is a mandatory variable.
- 8 => createMD5 -> TRUE or FALSE to create the file at the final destination with MD5 name. By default FALSE. There is a mandatory variable.
- 9 => resizeImage -> TRUE or FALSE to cut type image files by the width dynamically and make an automatic quality treatment by 90%. By default FALSE. There is a mandatory variable.
- 10 => maxWidthResize -> Width chosen for the image, if not pass anything and the image is larger than 1920px, cut in 1920px dynamically. There is a mandatory variable.
- 11 => zipArchive -> TRUE or FALSE to compress the file and send to the server. By default FALSE. There is a mandatory variable.
- 11 => unzipFinalArchive -> TRUE or FALSE to decompress the file at the final destination. By default FALSE. There is a mandatory variable.

## Use

#### Upload

Example using '$_FILES[]';

```php
<?php

    use \MBUpload\Upload.php as UploadArchives
    
    class Example extends UploadArchives {
    
        public function __construct($obj = array()) {
            foreach ($obj as $key => $value) {
                $this->$key = $value;
            }
        }
        
        private function upload() {
            $rs = self::uploadInServer($_FILES['file']['name'] , $_FILES['file']['size'], $_FILES['file']['tmp_name_'], '/home/user/msborges/images', '/new/', ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'JPG','JPEG', 'GIF', 'PNG', 'BMP'], 10485760, true, false, 0, false, false);
            if ($rs['status']) {
                echo $rs['data']['newName'];
            } else {
                echo $rs['error'] . ' - ' . $rs['msg'];
            }
        }
    }
```

#### Delete

```php
<?php

    use \MBUpload\Delete.php as DeleteArchives
    
    class Example extends DeleteArchives {
    
        public function __construct($obj = array()) {
            foreach ($obj as $key => $value) {
                $this->$key = $value;
            }
        }
        
        private function delete() {
            $rs = self::deleteInServer($_FILES['file']['name'], '/home/user/msborges/images', '/new/');
            if ($rs['status']) {
                echo $rs['msg'];
            } else {
                echo $rs['error'] . ' - ' . $rs['msg'];
            }
        }
    }
```

## Example within the git project in the example folder.

LICENSE
-------

_Copyright (C) 2015-2016, @msborges
