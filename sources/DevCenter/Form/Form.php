<?php
/**
 * @brief Form Class
 * @copyright -storm_copyright-
 * @package IPS Social Suite
 * @subpackage cjtracker
 * @since -storm_since_version-
 * @version -storm_version-
 */


namespace IPS\toolbox\DevCenter;

use function class_exists;

use IPS\Log;
use IPS\Helpers\Form\Text;
use IPS\Helpers\Form\Matrix;
use IPS\Helpers\Form\FormAbstract;
use IPS\Member;
use IPS\Helpers\Form;
use function defined;
use function header;
use function in_array;
use function is_object;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Form Class
 *
 * @mixin \IPS\cjtracker\Form
 */
class _Form
{

    /**
     * @var Form
     */
    protected static $nodeTitle = [];

    /**
     * @brief the class map for form elements
     * @var array
     */
    protected static $classMap = [
        'address' => 'Address',
        'addy' => 'Address',
        'captcha' => 'Captcha',
        'checkbox' => 'Checkbox',
        'cb' => 'Checkbox',
        'checkboxset' => 'CheckboxSet',
        'cbs' => 'CheckboxSet',
        'codemirror' => 'Codemirror',
        'cm' => 'Codemirror',
        'color' => 'Color',
        'custom' => 'Custom',
        'date' => 'Date',
        'daterange' => 'DateRange',
        'dr' => 'DateRange',
        'editor' => 'Editor',
        'email' => 'Email',
        'ftp' => 'Ftp',
        'item' => 'Item',
        'keyvalue' => 'KeyValue',
        'kv' => 'KeyValue',
        'matrix' => 'Matrix',
        'member' => 'Member',
        'node' => 'Node',
        'number' => 'Number',
        '#' => 'Number',
        'password' => 'Password',
        'pw' => 'Password',
        'poll' => 'Poll',
        'radio' => 'Radio',
        'rating' => 'Rating',
        'search' => 'Search',
        'select' => 'Select',
        'socialgroup' => 'SocialGroup',
        'sg' => 'SocialGroup',
        'sort' => 'Sort',
        'stack' => 'Stack',
        'Telephone' => 'Tel',
        'tel' => 'Tel',
        'text' => 'Text',
        'textarea' => 'TextArea',
        'ta' => 'TextArea',
        'timezone' => 'TimeZone',
        'translatable' => 'Translatable',
        'trans' => 'Translatable',
        'upload' => 'Upload',
        'up' => 'Upload',
        'url' => 'Url',
        'widthheight' => 'WidthHeight',
        'wh' => 'WidthHeight',
        'yesno' => 'YesNo',
        'yn' => 'YesNo',
    ];

    /**
     * @brief for use in run once the object is instantiated
     * @var Form
     */
    protected $form = \null;

    /**
     * @brief form helpers store
     * @var array
     */
    protected $elements = [];

    /**
     * @brief the form record object
     * @var null
     */
    protected $obj = \null;

    /**
     * @brief the language prefix
     * @var null
     */
    protected $langPrefix = \null;

    /**
     * @brief add header template
     * @var bool
     */
    protected $suffix = \false;

    /**
     * @brief header store
     * @var null
     */
    protected $header = \null;

    /**
     * @brief tab store
     * @var null
     */
    protected $tab = \null;

    /**
     * @var array
     */
    protected $bitOptions = [];

    /**
     * @var array
     */
    protected $props = [];

    /**
     * @var \IPS\Lang
     */
    protected $lang = \null;

    /**
     * _Forms constructor
     *
     * @param array $elements array of form elements
     * @param string|null $prefix language prefix for the form elements
     * @param array $config extra settings for the form
     */
    public function __construct(array $elements, string $prefix, array $config)
    {
        $this->elements = $elements;
        $this->langPrefix = $prefix;
        $this->lang = Member::loggedIn()->language();
        if (isset($config[ 'object' ])) {
            $this->obj = $config[ 'object' ];
        }
        
        $form = $config[ 'form' ] ?? \null;
        
        if ($form instanceof Form) {
            $this->form = $form;
        } else {
            $this->form = new Form($config[ 'id' ] ?? 'form', $config[ 'submitLang' ] ?? 'save',
            $config[ 'action' ] ?? \null, $config[ 'attributes' ] ?? []);
        }
        
        if (!isset($config[ 'suffix' ])) {
            $this->suffix = \true;
        }
        
        if (isset($config[ 'id' ])) {
            $this->form->id = $config[ 'id' ];
        }
        
        if (isset($config[ 'bitOptions' ])) {
            $this->bitOptions = $config[ 'bitOptions' ];
        }
        
        if (isset($config[ 'props' ])) {
            $this->props = $config[ 'props' ];
        }
    }

    /**
     * @param array $elements array of form elements
     * @param string|null $prefix language prefix for the form elements
     * @param array $config extra settings for the form
     * @return Form
     */
    public static function buildForm(array $elements, string $prefix, array $config = []):Form
    {
        /**
        * @var $class static
        */
        $class = new static($elements, $prefix, $config);
        return $class->build();
    }

    /**
     * @return Form
     */
    public function build():Form
    {
        $typesWName = [
            'tab',
            'header',
            'sidebar',
            'helper',
            'dummy',
            'matrix',
            'hidden',
        ];
        
        foreach ($this->elements as $el) {
            if ($el instanceof FormAbstract) {
                $this->form->add($el);
                continue;
            }
        
            if (empty($el)) {
                continue;
            }
        
            $type = $el[ 'type' ] ?? 'helper';
            $name = \null;
        
            if (in_array($type, $typesWName, \true)) {
                if (!isset($el[ 'name' ])) {
                    Log::debug('Form Helper type requires a name!');
                    continue;
                }
        
                $skip = $el[ 'skip' ] ?? \false;
                if (!$skip) {
                    $name = $this->langPrefix . $el[ 'name' ];
                } else {
                    $name = $el[ 'name' ];
                }
            }
        
            $this->extra($el);
        
            switch ($type) {
                case 'tab':
                    $suffix = $this->suffix ? '_tab' : '';
                    $names = $name . $suffix;
                    $this->form->addTab($names);
                    break;
                case 'header':
                    $this->form->addHeader($name . '_header');
                    break;
                case 'sidebar':
                    if ($this->lang->checkKeyExists($name)) {
                        $name = $this->lang->addToStack($name);
                    }
        
                    $this->form->addSidebar($name);
                    break;
                case 'separator':
                    $this->form->addSeparator();
                    break;
                case 'message':
                    $lang = $el[ 'msg' ] ?? \null;
        
                    if ($lang === \null) {
                        continue 2;
                    }
        
                    $css = $el[ 'css' ] ?? '';
                    $parse = $el[ 'parse' ] ?? \false;
                    $id = $el[ 'id' ] ?? \null;
                    $this->form->addMessage($lang, $css, $parse, $id);
                    break;
                case 'helper':
                    $customClass = $el[ 'customClass' ] ?? \false;
        
                    if ($customClass === \false) {
                        $class = $el[ 'class' ] ?? Text::class;
                        if ($class !== Text::class) {
                            $class = static::$classMap[ $class ] ?? $class;
                            $class = '\\IPS\\Helpers\\Form\\' . $class;
                        }
                    } else {
                        $class = $el[ 'customClass' ];
                    }
        
        
                    if (!class_exists($class, \true)) {
                        Log::debug('invale form class ' . $class);
                        continue 2;
                    }
        
                    $required = $el[ 'required' ] ?? \false;
                    $options = $el[ 'options' ] ?? $el[ 'ops' ] ?? [];
                    $validation = $el[ 'validation' ] ?? $el[ 'val' ] ?? \null;
                    $prefix = $el[ 'prefix' ] ?? \null;
                    $suffix = $el[ 'suffix' ] ?? \null;
                    $id = $el[ 'id' ] ?? \null;
                    $default = $el[ 'default' ] ?? $el[ 'def' ] ?? \null;
        
                    if ($id === \null && !isset($el[ 'skip_id' ])) {
                        $id = 'js_' . $name;
                    }
        
                    if ($default === \null) {
                        $obj = $this->obj;
                        $props = $this->props;
                        $prop = $el[ 'name' ];
                        $prop2 = $this->langPrefix . $prop;
                        if (is_object($this->obj) && empty($props)) {
                            $default = $obj->{$prop} ?? $obj->{$prop2} ?? \null;
                        }
        
                        if ($default === \null && !empty($props)) {
                            $default = $props[ $prop ] ?? $props[ $prop2 ] ?? \null;
                        }
        
                        if ($default === \null && !empty($this->bitOptions) && is_object($this->obj)) {
                            /* @var array $val */
                            foreach ($this->bitOptions as $key => $val) {
                                $break = \false;
                                foreach ($val as $k => $v) {
                                    if (!empty($obj->{$k}[ $prop ])) {
                                        $default = $obj->{$k}[ $prop ];
                                        $break = \true;
                                        break;
                                    }
        
                                    if (!empty($obj->{$k}[ $prop2 ])) {
                                        $default = $obj->{$k}[ $prop2 ];
                                        $break = \true;
                                        break;
                                    }
                                }
                                if ($break) {
                                    break;
                                }
                            }
                        }
                    }
        
                    /* @var array $toggles */
                    if (!empty($options)) {
                        if (isset($options[ 'toggles' ])) {
                            $toggles = $options[ 'toggles' ];
                            unset($options[ 'toggles' ]);
                            foreach ($toggles as $key => $val) {
                                foreach ($val as $k => $v) {
                                    $options[ 'toggles' ][ $key ][] = 'js_' . $this->langPrefix . $v;
                                }
                            }
                        }
        
                        if (isset($options[ 'togglesOn' ])) {
                            $toggles = $options[ 'togglesOn' ];
                            unset($options[ 'togglesOn' ]);
                            foreach ($toggles as $key => $val) {
                                $options[ 'togglesOn' ][] = 'js_' . $this->langPrefix . $val;
                            }
                        }
        
                        if (isset($options[ 'togglesOff' ])) {
                            $toggles = $options[ 'togglesOff' ];
                            unset($options[ 'togglesOff' ]);
                            foreach ($toggles as $key => $val) {
                                $options[ 'togglesOff' ][] = 'js_' . $this->langPrefix . $val;
                            }
                        }
        
                        //no append
                        /* @var array $naoptions */
                        if (isset($options[ 'natoggles' ])) {
                            $naoptions = $options[ 'natoggles' ];
                            foreach ($naoptions as $key => $val) {
                                foreach ($val as $k => $v) {
                                    $options[ 'toggles' ][ $key ][] = $v;
                                }
                            }
                        }
        
                        /* @var array $natogglesOn */
                        if (isset($options[ 'natogglesOn' ])) {
                            $natogglesOn = $options[ 'natogglesOn' ];
                            foreach ($natogglesOn as $key => $val) {
                                $options[ 'togglesOn' ][] = $val;
                            }
                        }
        
                        /* @var array $naTogglesOff */
                        if (isset($options[ 'natogglesOff' ])) {
                            $naTogglesOff = $options[ 'natogglesOff' ];
                            foreach ($naTogglesOff as $key => $val) {
                                $options[ 'togglesOff' ][] = $val;
                            }
                        }
                    }
        
                    $element = new $class($name, $default, $required, $options, $validation, $prefix, $suffix, $id);
        
                    $element->appearRequried = $el[ 'appearRequired' ] ?? $el[ 'ap' ] ?? \false;
        
                    if (isset($el[ 'label' ])) {
                        $label = $el[ 'label' ];
                        if ($this->lang->checkKeyExists($label)) {
                            $label = $this->lang->addToStack($label);
                        }
                        $element->label = $label;
                    }
        
                    if (isset($el[ 'description' ])) {
                        $desc = $el[ 'description' ];
                        if ($this->lang->checkKeyExists($desc)) {
                            if (isset($el[ 'desc_sprintf' ])) {
                                $sprintf = $el[ 'desc_sprintf' ];
                                $sprintf = (array)$sprintf;
                                $desc = $this->lang->addToStack($desc, \false, ['sprintf' => $sprintf]);
                            } else {
                                $desc = $this->lang->addToStack($desc);
                            }
                        }
        
                        $this->lang->words[ $name . '_desc' ] = $desc;
                    }
        
                    $this->form->add($element);
                    break;
                case 'dummy':
                    $default = $el[ 'default' ] ?? \null;
                    $desc = '';
                    if (isset($el[ 'desc' ])) {
                        if ($this->lang->checkKeyExists($el[ 'desc' ])) {
                            $desc = $this->lang->addToStack($el[ 'desc' ]);
                        } else {
                            $desc = $el[ 'desc' ];
                        }
                    }
        
                    $warning = '';
        
                    if (isset($el[ 'warning' ])) {
                        if ($this->lang->checkKeyExists($el[ 'warning' ])) {
                            $warning = $this->lang->addToStack($el[ 'warning' ]);
                        } else {
                            $warning = $el[ 'warning' ];
                        }
                    }
        
                    if (isset($el[ 'id' ])) {
                        $id = $el[ 'id' ];
                    } else {
                        $id = $name . '_js';
                    }
        
                    $this->form->addDummy($name, $default, $desc, $warning, $id);
                    break;
                case 'html':
                    if (!isset($el[ 'html' ])) {
                        continue 2;
                    }
                    $this->form->addHtml($el[ 'html' ]);
                    break;
                case 'matrix':
                    if (isset($el[ 'matrix' ]) && !($el[ 'matrix' ] instanceof Matrix)) {
                        continue 2;
                    }
        
                    $this->form->addMatrix($name, $el[ 'matrix' ]);
                    break;
                case 'hidden':
                    $this->form->hiddenValues[ $name ] = $el[ 'default' ];
                    break;
            }
        }
        
        return $this->form;
    }

    /**
     * @param array $el
     */
    public function extra(array $el)
    {
        $suffix = $this->suffix ? '_tab' : '';
        
        if (isset($el[ 'tab' ])) {
            $tab = $this->langPrefix . $el[ 'tab' ] . $suffix;
            $this->tab = $tab;
            $this->form->addTab($tab);
            unset($el[ 'tab' ]);
        }
        
        $suffix = $this->suffix ? '_header' : '';
        if (isset($el[ 'header' ]) && $this->header !== $this->langPrefix . $el[ 'header' ] . $suffix) {
            $header = $this->langPrefix . $el[ 'header' ] . $suffix;
            $this->header = $header;
            $this->form->addHeader($header);
            unset($el[ 'header' ]);
        }
        
        if (isset($el[ 'sidebar' ])) {
            $sideBar = $this->langPrefix . $el[ 'sidebar' ] . '_sidebar';
            if ($this->lang->checkKeyExists($sideBar)) {
                $sideBar = $this->lang->addToStack($sideBar);
            }
        
            $this->form->addSidebar($sideBar);
            unset($el[ 'sidebar' ]);
        }
    }


}

