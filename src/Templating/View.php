<?php


namespace Sifra\Templating;


use Sifra\Core\Exception\NotFoundException;

class View
{
    private $path;
    private $viewData;
    private $layoutPath;
    private $title;

    public function __construct($path, array $viewData = array())
    {
        $this->path = $path;
        $this->viewData = $viewData;
    }

    public function render()
    {
        if (!$this->hasLayout()) {
            return self::createContent($this->path);
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function partial($path)
    {
        echo self::createContent($path, $this->viewData);
    }

    public function layout($layoutPath)
    {
        $this->layoutPath = $layoutPath;
    }

    public function hasLayout()
    {
        return $this->layoutPath && self::exists($this->layoutPath);
    }

    public static function createContent($path, array $data = array())
    {
        $data = parseInputArray($data);

        foreach ($data as $k => $v) {
            ${$k} = $v;
        }

        ob_start();
        include self::locateOrThrow($path);
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public static function locate($path)
    {
        $path = rtrim(settings('paths.views')) . DIRECTORY_SEPARATOR . sanitizePath($path);

        if (!pathinfo($path, PATHINFO_EXTENSION)) {
            $path .= '.php';
        }

        return $path;
    }

    public static function locateOrThrow($path)
    {
        $path = self::locate($path);

        if (file_exists($path)) {
            return $path;
        }

        throw new NotFoundException("View { $path } does not exist.");
    }

    public static function exists($path)
    {
        return file_exists(self::locate($path));
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getViewData()
    {
        return $this->viewData;
    }
}