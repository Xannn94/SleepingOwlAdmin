<?php

namespace SleepingOwl\Admin\Display;

use SleepingOwl\Admin\Traits\Assets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use KodiComponents\Support\HtmlAttributes;
use SleepingOwl\Admin\Contracts\ColumnInterface;
use SleepingOwl\Admin\Display\Column\OrderByClause;
use SleepingOwl\Admin\Contracts\ModelConfigurationInterface;
use SleepingOwl\Admin\Contracts\Display\OrderByClauseInterface;
use SleepingOwl\Admin\Contracts\Display\TableHeaderColumnInterface;

abstract class TableColumn implements ColumnInterface
{
    use HtmlAttributes, Assets;

    /**
     * Column header.
     *
     * @var TableHeaderColumnInterface
     */
    protected $header;

    /**
     * Model instance currently rendering.
     *
     * @var Model
     */
    protected $model;

    /**
     * Column appendant.
     *
     * @var ColumnInterface
     */
    protected $append;

    /**
     * Column width.
     *
     * @var string
     */
    protected $width = null;

    /**
     * @var string|\Illuminate\View\View
     */
    protected $view;

    /**
     * @var OrderByClauseInterface
     */
    protected $orderByClause;

    /**
     * TableColumn constructor.
     *
     * @param string|null $label
     */
    public function __construct($label = null)
    {
        $this->header = app(TableHeaderColumnInterface::class);

        if (! is_null($label)) {
            $this->setLabel($label);
        }

        $this->initializePackage();
    }

    /**
     * Initialize column.
     */
    public function initialize()
    {
        $this->includePackage();
    }

    /**
     * @return TableHeaderColumnInterface
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return string|\Illuminate\View\View
     */
    public function getView()
    {
        if (is_null($this->view)) {
            $reflect = new \ReflectionClass($this);
            $this->view = 'column.'.strtolower($reflect->getShortName());
        }

        return $this->view;
    }

    /**
     * @param string|\Illuminate\View\View $view
     *
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return ColumnInterface
     */
    public function getAppends()
    {
        return $this->append;
    }

    /**
     * @param ColumnInterface $append
     *
     * @return $this
     */
    public function append(ColumnInterface $append)
    {
        $this->append = $append;

        return $this;
    }

    /**
     * @return Model $model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        $append = $this->getAppends();

        if (! is_null($append)) {
            $append->setModel($model);
        }

        return $this;
    }

    /**
     * Get related model configuration.
     * @return ModelConfigurationInterface
     */
    protected function getModelConfiguration()
    {
        return app('sleeping_owl')->getModel(get_class($this->getModel()));
    }

    /**
     * Set column header label.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setLabel($title)
    {
        $this->getHeader()->setTitle($title);

        return $this;
    }

    /**
     * @param OrderByClauseInterface|bool|string|\Closure $orderable
     *
     * @return $this
     */
    public function setOrderable($orderable)
    {
        if ($orderable instanceof \Closure || is_string($orderable)) {
            $orderable = new OrderByClause($orderable);
        }

        if ($orderable !== false && ! $orderable instanceof OrderByClauseInterface) {
            throw new \InvalidArgumentException('Argument must be instance of SleepingOwl\Admin\Contracts\Display\OrderByClauseInterface interface');
        }

        $this->orderByClause = $orderable;
        $this->getHeader()->setOrderable($this->isOrderable());

        return $this;
    }

    /**
     * Check if column is orderable.
     * @return bool
     */
    public function isOrderable()
    {
        return $this->orderByClause instanceof OrderByClauseInterface;
    }

    /**
     * @param Builder $query
     * @param $condition
     */
    public function orderBy(Builder $query, $condition)
    {
        if (! $this->isOrderable()) {
            throw new \InvalidArgumentException('Argument must be instance of SleepingOwl\Admin\Contracts\Display\OrderByClauseInterface interface');
        }

        $this->orderByClause->modifyQuery($query, $condition);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'attributes' => $this->htmlAttributesToString(),
            'model'      => $this->getModel(),
            'append' => $this->getAppends(),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }

    /**
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function render()
    {
        return app('sleeping_owl.template')->view(
            $this->getView(),
            $this->toArray()
        );
    }
}
