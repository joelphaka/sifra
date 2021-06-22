<?php
/**
 * Created by PhpStorm.
 * User: Joel
 * Date: 2018/12/28
 * Time: 16:47
 */

namespace Sifra\IO;


use Sifra\Http\Exception\HttpException;
use \SplFileInfo;

class UploadedFile
{
    private $name;
    private $tempName;
    private $cLentName;
    private $type;
    private $extension;
    private $size;
    private $hasError;

    public function __construct($name)
    {
        if (!request()->hasFile($name)) {
            throw new HttpException("File { $filename } is not an uploaded file.");
        }

        $this->name = $name;
        $this->tempName = $_FILES[$name]['tmp_name'];
        $this->cLentName = $_FILES[$name]['name'];
        $this->type = $_FILES[$name]['type'];
        $this->extension = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
        $this->size = $_FILES[$name]['size'];
        $this->hasError = (bool)$_FILES[$name]['error'];
    }

    public function move($destination)
    {
        return move_uploaded_file($this->getTempName(), $destination);
    }

    public function saveAs($destination)
    {
        return $this->move($destination);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTempName()
    {
        return $this->tempName;
    }

    /**
     * @return mixed
     */
    public function getCLentName()
    {
        return $this->cLentName;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->hasError;
    }


}