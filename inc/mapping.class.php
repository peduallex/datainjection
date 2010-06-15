<?php
/*
 * @version $Id: rules.constant.php 5351 2007-08-07 11:57:46Z walid $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */
class PluginDatainjectionMapping extends CommonDBTM {

   //const NOT_MAPPED = -1;

   function canCreate() {
      return plugin_datainjection_haveRight('model','w');
   }

   function canView() {
      return plugin_datainjection_haveRight('model','r');
   }

   /*
    *
    */
   function equal($field,$value)
   {
      if (!isset($this->fields[$field]))
         return false;

      if ($this->fields[$field] == $value)
         return true;
      else
         return false;
   }

   function isMandatory()
   {
      return $this->fields["is_mandatory"];
   }

   function getMappingName()
   {
      return $this->fields["name"];
   }

   function getRank()
   {
      return $this->fields["rank"];
   }

   function getValue()
   {
      return $this->fields["value"];
   }

   function getID()
   {
      return $this->fields["id"];
   }

   function getModelID()
   {
      return $this->fields["models_id"];
   }

   function getItemtype()
   {
      return $this->fields["itemtype"];
   }

   function setMandatory($mandatory)
   {
      $this->fields["is_mandatory"] = $mandatory;
   }

   function setName($name)
   {
      $this->fields["name"] = $name;
   }

   function setRank($rank)
   {
      $this->fields["rank"] = $rank;
   }

   function setValue($value)
   {
      $this->fields["value"] = $value;
   }

   function setID($ID)
   {
      $this->fields["id"] = $ID;
   }

   function setModelID($model_id)
   {
      $this->fields["models_id"] = $model_id;
   }

   function setItemtype($type)
   {
      $this->fields["itemtype"] = $type;
   }

   static function showFormMappings(PluginDatainjectionModel $model) {
      global $LANG, $DB,$CFG_GLPI;

      $canedit=$model->can($model->fields['id'],'w');
      if (isset($_SESSION['datainjection']['lines'])) {
         $lines = unserialize($_SESSION['datainjection']['lines']);
      }
      else {
         $lines = array();
      }

      echo "<form method='post' name=form action='".getItemTypeFormURL(__CLASS__)."'>";

      //Display link to the preview popup
      if (isset($_SESSION['datainjection']['lines'])
            && !empty($lines)) {
         $nblines = $_SESSION['datainjection']['nblines'];
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><td align='center'>";
         $url = $CFG_GLPI["root_doc"].
              "/plugins/datainjection/front/popup.php?popup=preview&amp;models_id=".$model->fields['id'];
         echo "<a href=#  onClick=\"var w = window.open('$url' ,";
         echo "'glpipopup', 'height=400, width=600, top=100, left=100, scrollbars=yes' );w.focus();\"/>";
         echo $LANG["datainjection"]["button"][3]."</a>";
         echo "</td></tr>";
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>" . $LANG["datainjection"]["mapping"][2] . "</th>";
      echo "<th>" . $LANG["datainjection"]["mapping"][3] . "</th>";
      echo "<th>" . $LANG["datainjection"]["mapping"][4] . "</th>";
      echo "<th>" . $LANG["datainjection"]["mapping"][5] . "</th>";
      echo "</tr>";

      $model->loadMappings();

      foreach ($model->getMappings() as $mapping) {
         $mapping->fields = stripslashes_deep($mapping->fields);
         $mappings_id = $mapping->fields['id'];
         echo "<tr class='tab_bg_1'>";
         echo "<td align='center'>".$mapping->fields['name']."</td>";
         echo "<td align='center'>";
         $options =  array('primary_type' => $model->fields['itemtype']);
         $rand = PluginDatainjectionInjectionType::dropdownLinkedTypes($mapping,$options);
         echo "</td>";
         $options['mapping_or_info']= $mapping->fields;
         $options['called_by']      = __CLASS__;
         $options['need_decode']    = false;
         $options['itemtype']= $mapping->fields['itemtype'];
         $options['fields_update']= false;

         echo "<td align='center'><span id='span_field_$mappings_id'>";
         //PluginDatainjectionDropdown::dropdownFields($options);
         echo "</span></td>";
         //PluginDatainjectionDropdown::showMandatoryCheckbox($options);
         echo "<td align='center'><span id='span_mandatory_$mappings_id'></span></td>";
      }

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='4'>";
         echo "<input type='hidden' name='models_id' value='".$model->fields['id']."'>";
         echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";
         echo "</td></tr>";
      }
      echo "</table></form>";
   }

   /**
    * For multitext only ! Check it there's more than one value to inject in a field
    * @model the model
    * @mapping_definition the mapping_definition corresponding to the field
    * @value the value of the mapping
    * @line the complete line to inject
    * @return true if more than one value to inject, false if not
    */
   static function getSeveralMappedField($models_id) {
      global $DB;
      $several = array();
      $query  = "SELECT value, COUNT(*) AS `counter` FROM `glpi_plugin_datainjection_mappings` ";
      $query .= "WHERE `models_id`='$models_id' AND `value` NOT IN ('none') ";
      $query .= "GROUP BY `value` HAVING `counter` > 1";
      foreach ($DB->request($query) as $mapping) {
         $several[] = $mapping['value'];
      }
      return $several;
   }

}
?>