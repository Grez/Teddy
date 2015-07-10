<?php

namespace Teddy\Forms;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Forms\Container;
use Nette;
use Nette\Forms\Controls;


/**
 * @method \Nella\Forms\DateTime\DateInput addDate($name, $label = NULL, $format = NULL)
 * @method \Nella\Forms\DateTime\DateTimeInput addDateTime($name, $label = NULL, $dateFormat = NULL, $timeFormat = NULL)
 */
class Form extends \Nette\Application\UI\Form
{

    /** @var BaseEntity */
    protected $entity;


    /**
     * @param BaseEntity $entity
     */
    public function bindEntity(BaseEntity $entity)
    {
        $this->entity = $entity;
        $this->fillComponents($this->getComponents());
    }

    /**
     * Fills form's components with data from entity
     * @param \ArrayIterator $components
     */
    protected function fillComponents($components)
    {
        foreach ($components as $name => $input) {
            if ($input instanceof Container) {
                $this->fillComponents($input->getComponents());
            } else {
                try {
                    $method = "get$name";
                    $value = $this->entity->$method();
                } catch (\Kdyby\Doctrine\MemberAccessException $e) {
                    continue;
                }

                $value = $this->entity->$method();

                if ($value instanceof BaseEntity) {
                    $value = $value->getId();
                } elseif ($value instanceof ArrayCollection || $value instanceof PersistentCollection) {
                    $value = array_map(function (BaseEntity $entity) {
                        return $entity->getId();
                    }, $value->toArray());
                }

                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $input->setDefaultValue($value);
            }
        }
    }

    /**
     * Sets Bootstrap 3 rendering
     * @TODO: maybe separate this into another file?
     * @return Nette\Forms\IFormRenderer
     */
    public function getRenderer()
    {
        $renderer = parent::getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

        // make form and controls compatible with Twitter Bootstrap
        $this->getElementPrototype()->class('form-horizontal');

        foreach ($this->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                if ($control->getSeparatorPrototype()->getName() == 'inline') {
                    $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type . '-inline');
                } else {
                    $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
                }
            }
        }

        return $renderer;
    }

}