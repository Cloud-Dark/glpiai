<?php

/**
 * -------------------------------------------------------------------------
 * Example plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Example.
 *
 * Example is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Example is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Example. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2006-2022 by Example plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/example
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Openrouter;

use Glpi\Application\View\TemplateRenderer;
use Html;
use Ticket;

/**
 * Summary of GlpiPlugin\Example\ItemForm
 * Example of *_item_form implementation
 * @see http://glpi-developer-documentation.rtfd.io/en/master/plugins/hooks.html#items-display-related
 * */
class ItemForm
{
    /**
     * Display contents at the begining of ITILObject section (right panel).
     *
     * @param array $params Array with "item" and "options" keys
     *
     * @return void
     */
    public static function preSection($params)
    {
        global $DB;
        $item    = $params['item'];
        $options = $params['options'];

        $ticket = $params['item'];
        $ticket_id = 0;
        if (!is_null($ticket) || $ticket->getID()) {
            $ticket_id = $ticket->getID();
        }

    $is_disabled = 0;
    if ($ticket_id > 0) {
        $table_name = 'glpi_plugin_openrouter_disabled_tickets';
        $query = "SELECT `tickets_id` FROM `$table_name` WHERE `tickets_id` = '$ticket_id'";
        $result = $DB->doQuery($query);
        $is_disabled = $DB->numrows($result) > 0;
    }
	file_put_contents('/tmp/openrouter_debug.log',date('c') . "is_disabled" .$is_disabled . PHP_EOL,FILE_APPEND);
        echo TemplateRenderer::getInstance()->renderFromStringTemplate(<<<TWIG
      <section class="accordion-item" aria-label="a label">
      <div class="accordion-header" id="openrouter-heading" title="openrouter-heading-id" data-bs-toggle="tooltip">
         <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#openrouter-pre-content" aria-expanded="true" aria-controls="openrouter-pre-content">
            <i class="ti ti-world me-1"></i>
            <span class="item-title">
               AI Assistant Options
            </span>
         </button>
      </div>
      <div id="openrouter-pre-content" class="accordion-collapse collapse" aria-labelledby="openrouter-pre-content-heading">
         <div class="accordion-body">
            <input type="checkbox" name="openrouter_bot_disabled" value="1" {% if is_disabled == 1 %}checked{% endif %}  id="openrouter_bot_disabled_checkbox">
                <label for="openrouter_bot_disabled_checkbox">Disable OpenRouter Bot for this ticket</label>
            </input>
         </div>
      </div>
   </section>
TWIG, ['is_disabled' => $is_disabled]);
    }
}