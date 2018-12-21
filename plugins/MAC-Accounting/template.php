<?php

include_once 'classes/Form.php';

class Template
{
    public $properties;

    public function __construct()
    {
        $this->properties = array();
    }

    public function render($htmlFile)
    {
        ob_start();
        $path = "plugins/MAC-Accounting/html/".$htmlFile;
        include($path);
        return ob_get_clean();
    }

    public function __set($k, $v)
    {
        $this->properties[$k] = $v;
    }

    public function __get($k)
    {
        return $this->properties[$k];
    }

    ##### table functions interact with netharbour's Form class to create convenient/consistent tables

    public function tableCreate($rows, $columns, $sortable, $header, $width)
    {
        $form = new Form($rows, $columns);
        $form->setSortable($sortable);
        $form->setHeadings($header);
        $form->setTableWidth($width);
        return $form;
    }

    public function tableSet($form, $data)
    {
        $form->setData($data);
    }

    public function tableCheckBox($id, $checked)
    {
        return "<input type=checkbox name=devices[] value='$id' $checked >";
    }

    public function tableHTML($form)
    {
        return $form->showForm();
    }

    public function tableHandler($form, $handler)
    {
        $form->setEventHandler($handler);
    }
}