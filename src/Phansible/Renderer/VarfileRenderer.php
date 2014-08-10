<?php
/**
 * Var File renderer
 */

namespace Phansible\Renderer;

use Phansible\Model\AbstractFileRenderer;

class VarfileRenderer extends AbstractFileRenderer
{
    protected $data;
    protected $filename;
    protected $template;

    /**
     * @param $filename
     */
    public function  __construct($filename)
    {
        $this->filename = $filename;
        parent::__construct();
    }

    public function loadDefaults()
    {
        $this->template = 'vars.yml.twig';
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function add($key, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->data[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Returns the data for the template
     * @return Array
     */
    public function getData()
    {
        return [ 'variables' => $this->data ];
    }

    /**
     * @param mixed $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * FilePath for saving the rendered template
     */
    public function getFilePath()
    {
        return 'ansible/vars/' . $this->filename . '.yml';
    }


} 