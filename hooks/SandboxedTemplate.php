//<?php

use IPS\Data\Store;
use IPS\Member;
use IPS\Request;
use IPS\Settings;

if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header(($_SERVER[ 'SERVER_PROTOCOL' ] ?? 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

class toolbox_hook_SandboxedTemplate extends _HOOK_CLASS_
{

    public function __call($name, $args)
    {
        $can = \json_decode(Settings::i()->dtprofiler_can_use, \true);

        if (!Request::i()->isAjax() && \IPS\QUERY_LOG && Settings::i()->dtprofiler_enabled_templates && \in_array(
                Member::loggedIn()->member_id,
                $can
            ) && $this->template->app !== 'dtprofiler' && ($this->template->app === 'core' && $name === 'cachingLog')) {
            if (isset(Store::i()->dtprofiler_templates)) {
                $log = Store::i()->dtprofiler_templates;
            }

            $log[] = [
                'name'     => $name,
                'group'    => $this->template->templateName,
                'location' => $this->template->templateLocation,
                'app'      => $this->template->app,
            ];

            Store::i()->dtprofiler_templates = $log;
        }

        return parent::__call($name, $args);
    }
}
