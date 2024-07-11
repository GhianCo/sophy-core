<?php

namespace Sophy\Helpers;

class File
{

    /**
     * Check if a file exists at the given path.
     *
     * @param string $path The path to the file.
     * @return bool Returns true if the file exists, false otherwise.
     */
    public static function exists($path)
    {
        return file_exists($path);
    }


    /**
     * Run the PHP code in the file at the given path.
     *
     * @param string $path The path to the file.
     * @return bool Returns true if the file was successfully included, false otherwise.
     */
    public static function run($path)
    {
        if (self::exists($path)) {
            include $path;
            return true;
        }
        return false;
    }



    /**
     * Run the PHP code in the file at the given path, but only once.
     *
     * @param string $path The path to the file.
     * @return bool Returns true if the file was successfully included once, false otherwise.
     */
    public static function runOnce($path)
    {
        if (self::exists($path)) {
            include_once $path;
            return true;
        }

        return false;
    }

    /**
     * Downloads a file from the server to the client with appropriate headers and content type.
     *
     * @param string $file_path The path of the file to download.
     * @param string|null $download_name The filename that will be shown in the download prompt. Defaults to the basename of the file_path if null.
     * @return void
     */
    public static function download($file_path, $download_name = null)
    {
        // Load FileInfo module to determine the MIME type of the file
        $file_info = finfo_open(FILEINFO_MIME_TYPE);

        if (is_file($file_path)) {
            $file_size = filesize($file_path);
            $download_name = $download_name ?? basename($file_path);

            // Determine the content type based on the MIME type of the file
            $content_type = finfo_file($file_info, $file_path);

            // Set headers for streaming
            header('Content-Type: ' . $content_type);
            header('Content-Transfer-Encoding: Binary');
            header('Content-Length: ' . $file_size);
            header('Content-disposition: attachment; filename="' . $download_name . '"');

            // Open the file and stream it to the output buffer in small chunks
            $file = fopen($file_path, 'rb');
            while (!feof($file)) {
                print(fread($file, 1024 * 8));
                ob_flush();
                flush();
            }
            fclose($file);
            exit;
        } else {
            echo "Error: File not found.";
        }

        finfo_close($file_info); // Close the FileInfo module
    }


    /**
     * Show an image.
     *
     * @param string $path The path to the image file.
     * @return string Returns the contents of the image file.
     */
    public static function showImage($path)
    {
        $name = basename($path);
        $file_ext = (explode('.', strtolower($name)));
        $file_ext = $file_ext[count($file_ext) - 1];

        switch ($file_ext) {

            case "gif":
                $ctype = "image/gif";
                break;

            case "png":
                $ctype = "image/png";
                break;

            case "jpeg":
            case "jpg":
                $ctype = "image/jpeg";
                break;

            case 'svg':
                $ctype = "image/svg+xml";
                break;
            default:
        }


        if (File::exists($path)) {
            header('Content-type: ' . $ctype);
            return file_get_contents($path);
        } else {
            http_response_code(404);
            return '404 file not found';
        }
    }


    /**
     * Delete a file.
     *
     * @param string $path The path to the file to delete.
     * @return bool Returns true if the file was successfully deleted, false otherwise.
     */
    public static function delete($path)
    {
        if (self::exists($path)) {

            return \unlink($path);
        }

        return false;
    }


    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $dir The path to the directory to delete.
     * @return bool Returns true if the directory was successfully deleted, false otherwise.
     */
    public static function delete_dir($dir)
    {

        $files = self::getFiles($dir);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delete_dir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * Get all files in a directory.
     *
     * @param string $path The path to the directory.
     * @param array $filter An array of filenames to exclude.
     * @return array Returns an array of files in the directory.
     */
    public static function getFiles($path, $filter = ['.', '..', '.gitignore'])
    {
        $files = [];
        $res = array_diff(scandir($path), $filter);
        foreach ($res as $key => $file) {
            $files[] = $file;
        }
        return $files;
    }



    /**
     * Get the content of a file.
     *
     * @param string $name The path to the file.
     * @return string Returns the content of the file.
     */
    public static function getContent($name)
    {
        $text = '';
        $myfile = fopen($name, "r");;
        $text = fread($myfile, filesize($name));
        fclose($myfile);
        return $text;
    }

    /**
     * Write content to a file.
     *
     * @param string $name The path to the file.
     * @param string $content The content to write.
     * @return void
     */
    public static function putContent($name, $content)
    {
        file_put_contents($name, $content);
    }

    /**
     * Get the MD5 hash of the file at the given path.
     *
     * @param  string  $path
     * @return string
     */
    public static function hash($path)
    {
        if (self::exists($path)) {
            return md5_file($path);
        }
        return false;
    }

    public static function recursiveCopy($source, $target)
    {
        if (is_dir($source)) {
            @mkdir($target, 0777, true);
            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $entryDir = $source . '/' . $entry;
                if (is_dir($entryDir)) {
                    self::recursiveCopy($entryDir, $target . '/' . $entry);
                    continue;
                }
                copy($entryDir, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }

    public static function replaceFileContent($target, $replacement, $valueToChange = 'objectbase')
    {
        $content1 = file_get_contents($target);
        if ($valueToChange == 'objectbase') {
            $content2 = preg_replace("/" . 'Objectbase' . "/", ucfirst($replacement), $content1);
            $content3 = preg_replace("/" . 'objectbase' . "/", $replacement, $content2);
        } else {
            $content3 = preg_replace("/" . $valueToChange . "/", $replacement, $content1);
        }
        file_put_contents($target, $content3);
    }

    public static function writeFile($fClass, $fName)
    {
        if (!$handle = fopen($fName, 'w')) {
            exit;
        }

        if (fwrite($handle, $fClass) === false) {
            exit;
        }
        fclose($handle);
    }

    public static function stringInFileFound($path, $valueFind)
    {
        $handle = fopen($path, 'r');
        $valid = false;

        while (($buffer = fgets($handle)) !== false) {
            if (strpos($buffer, $valueFind) !== false) {
                $valid = $valueFind;
                break;
            }
        }
        fclose($handle);

        return $valid;
    }
}
