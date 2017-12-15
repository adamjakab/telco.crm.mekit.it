<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/MVC/View/views/view.ajax.php');
require_once('include/EditView/EditView2.php');
require_once("modules/Calendar/CalendarUtils.php");
require_once('include/json_config.php');
require_once("data/BeanFactory.php");

/**
 * Class CalendarViewQuickEditRas
 */
class CalendarViewQuickEditRas extends SugarView
{
    /** @var string  */
    private $baseModuleName = "Cases";

    /**
     * @var boolean
     */
    protected $editable = false;

    /**
     * Do something before display
     */
    public function preDisplay()
    {
        if(isset($this->view_object_map['currentBean']))
        {
            $this->bean = $this->view_object_map['currentBean'];
            $this->editable = $this->bean->ACLAccess('Save');
        }
    }

    /**
     * Display
     */
    public function display()
    {
        //$moduleName = $this->baseModuleName;
        $_REQUEST['module'] = $this->baseModuleName;

        $gr = "";

        if (!empty($this->bean->id)) {
            global $json;
            $json = getJSONobj();
            $json_config = new json_config();
            $gr = $json_config->getFocusData($this->baseModuleName, $this->bean->id);
        }

        $json_arr = [
            'access' => 'yes',
            'module_name' => $this->bean->module_dir,
            'record' => $this->bean->id,
            'edit' => ($this->editable ? 1 : 0),
            'html' => $this->getMainDisplay(),
            'gr' => $gr
        ];

        if($repeat_arr = CalendarUtils::get_sendback_repeat_data($this->bean))
        {
            $json_arr = array_merge($json_arr,array("repeat" => $repeat_arr));
        }

        ob_clean();
        print json_encode($json_arr);
    }


    /**
     * @return string
     */
    protected function getMainDisplay()
    {
        $ss = new Sugar_Smarty();

        $ss->assign('FORM_CASES', $this->getCasesEditForm());
        //$ss->assign('FORM_ACCOUNTS', $this->getAccountEditForm());
        //$ss->assign('FORM_MEETINGS', $this->getMeetingsEditForm());


        $answer = $ss->fetch("custom/modules/Calendar/tpls/ras.tpl");

        return $answer;
    }




    /**
     * @return string
     */
    public function getCasesEditForm()
    {
        $moduleName = "Cases";
        $source = $this->getBestViewdefsPath($moduleName);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $moduleName);
        $tpl = $this->getCustomFilePathIfExists('include/EditView/EditView.tpl');


        $ev = new EditView();
        $ev->view = "QuickCreate";
        $ev->ss = new Sugar_Smarty();
        $ev->formName = "CalendarEditView";

        $this->bean->name = "Nuovo RAS di Adam";

        $ev->setup($moduleName, $this->bean, $source, $tpl);
        //$ev->defs['templateMeta']['form']['headerTpl'] = "modules/Calendar/tpls/editHeader.tpl";
        //$ev->defs['templateMeta']['form']['footerTpl'] = "modules/Calendar/tpls/empty.tpl";
        $ev->process(false, "CalendarEditView");


        $html = $ev->display(false, true);

        /*
        $data = [];
        $data[] = print_r($ev, true);
        $html = '<pre>' . htmlentities(implode("\n", $data)). '</pre>';
        */

        return $html;
    }

    /**
     *
     * @return string
     */
    public function getMeetingsEditForm()
    {
        $moduleName = "Meetings";
        $source = $this->getBestViewdefsPath($moduleName);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $moduleName);
        $tpl = $this->getCustomFilePathIfExists('include/EditView/EditView.tpl');



        $ev = new EditView();
        $ev->view = "QuickCreate";
        $ev->ss = new Sugar_Smarty();
        $ev->formName = "CalendarEditView";

        /** @var Meeting $bean */
        $bean = BeanFactory::getBean($moduleName);
        $bean->name = "Nuovo Meeting di Adam";



        $ev->setup($moduleName, $bean, $source, $tpl);



        //$ev->defs['templateMeta']['form']['headerTpl'] = "modules/Calendar/tpls/editHeader.tpl";
        //$ev->defs['templateMeta']['form']['footerTpl'] = "modules/Calendar/tpls/empty.tpl";

        $ev->process(false, "CalendarEditView");

        $html = '';

        //$html .= '<pre>DEFS: ' . htmlentities(print_r( $ev->defs, true)). '</pre>';
        //$html .= '<pre>FIELDDEFS: ' . htmlentities(print_r( $ev->fieldDefs, true)). '</pre>';



        $html .= $ev->display(false, true);



        return $html;
    }

    /**
     *
     * @return string
     */
    public function getAccountEditForm()
    {
        $moduleName = "Accounts";
        $source = $this->getBestViewdefsPath($moduleName);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], $moduleName);
        $tpl = $this->getCustomFilePathIfExists('include/EditView/EditView.tpl');


        //$ev = new EditView();

        $ev = new EditView();
        $ev->view = "QuickCreate";
        $ev->ss = new Sugar_Smarty();
        $ev->formName = "CalendarEditView";

        /** @var Meeting $bean */
        $bean = BeanFactory::getBean($moduleName);
        $bean->name = "Nuovo Account di Adam";



        $ev->setup($moduleName, $bean, $source, $tpl);



        //$ev->defs['templateMeta']['form']['headerTpl'] = "modules/Calendar/tpls/editHeader.tpl";
        //$ev->defs['templateMeta']['form']['footerTpl'] = "modules/Calendar/tpls/empty.tpl";

        $ev->process(false, "CalendarEditView");

        $html = '';

        //$html .= '<pre>DEFS: ' . htmlentities(print_r( $ev->defs, true)). '</pre>';
        //$html .= '<pre>FIELDDEFS: ' . htmlentities(print_r( $ev->fieldDefs, true)). '</pre>';



        $html .= $ev->display(false, true);



        return $html;
    }


    /**
     * @param \SugarBean $bean
     * @return mixed
     */
    public function isBeanEditable($bean)
    {
        return $bean->ACLAccess('Save');
    }

    /**
     * @param string $moduleName
     * @return string
     */
    protected function getBestViewdefsPath($moduleName)
    {
        $base = 'modules/' . $moduleName . '/metadata/';
        $source = 'custom/' . $base . 'quickcreatedefs.php';
        if (!file_exists($source)) {
            $source = $base . 'quickcreatedefs.php';
            if (!file_exists($source)) {
                $source = 'custom/' . $base . 'editviewdefs.php';
                if (!file_exists($source)) {
                    $source = $base . 'editviewdefs.php';
                }
            }
        }

        return $source;
    }
}
