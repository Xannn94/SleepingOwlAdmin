<?php

namespace SleepingOwl\Admin\Form\Element;

class Textarea extends NamedFormElement
{
    /**
     * @var int
     */
    protected $rows = 10;

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param int $rows
     *
     * @return $this
     */
    public function setRows($rows)
    {
        intval($rows);
        if ($rows < 1) {
            $rows = 1;
        }

        $this->rows = $rows;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + [
            'rows'     => $this->getRows(),
        ];
    }
}
