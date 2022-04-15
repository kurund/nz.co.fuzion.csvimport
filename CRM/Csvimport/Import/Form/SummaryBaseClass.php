<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class summarizes the import results
 */
class CRM_Csvimport_Import_Form_SummaryBaseClass extends CRM_Import_Form_Summary {

  /**
   * This is used in error urls
   * although this code specifies the Event import parser it is a completely generic function that could live anywhere (& probably does in C&P
   * manifestations
   *
   * @var unknown
   */
  protected $_importParserUrl = '&parser=CRM_Event_Import_Parser_Participant';

  /**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  public function preProcess() {
    // set the error message path to display
    $this->assign('errorFile', $this->get('errorFile'));

    $totalRowCount = $this->get('totalRowCount');
    $relatedCount = $this->get('relatedCount');
    $totalRowCount += $relatedCount;
    $this->set('totalRowCount', $totalRowCount);

    $invalidRowCount = $this->getInvalidRowCount();
    $conflictRowCount = $this->get('conflictRowCount');
    $duplicateRowCount = $this->get('duplicateRowCount');
    $onDuplicate = $this->get('onDuplicate');
    $mismatchCount = $this->get('unMatchCount');
    if ($invalidRowCount > 0) {
      $urlParams = 'type=' . CRM_Import_Parser::ERROR . $this->_importParserUrl;
      $this->set('downloadErrorRecordsUrl', CRM_Utils_System::url('civicrm/export', $urlParams));
      $this->set('invalidRowCount', $invalidRowCount);
    }
    elseif ($duplicateRowCount > 0) {
      $urlParams = 'type=' . CRM_Import_Parser::DUPLICATE . $this->_importParserUrl;
      $this->set('downloadDuplicateRecordsUrl', CRM_Utils_System::url('civicrm/export', $urlParams));
    }
    elseif ($mismatchCount) {
      $urlParams = 'type=' . CRM_Import_Parser::NO_MATCH . $this->_importParserUrl;
      $this->set('downloadMismatchRecordsUrl', CRM_Utils_System::url('civicrm/export', $urlParams));
    }
    else {
      $duplicateRowCount = 0;
      $this->set('duplicateRowCount', $duplicateRowCount);
    }

    $this->assign('dupeError', FALSE);

    if ($onDuplicate == CRM_Import_Parser::DUPLICATE_UPDATE) {
      $dupeActionString = ts('These records have been updated with the imported data.');
    }
    elseif ($onDuplicate == CRM_Import_Parser::DUPLICATE_FILL) {
      $dupeActionString = ts('These records have been filled in with the imported data.');
    }
    else {
      /* Skip by default */

      $dupeActionString = ts('These records have not been imported.');

      $this->assign('dupeError', TRUE);

      /* only subtract dupes from successful import if we're skipping */

      $this->set('validRowCount', $totalRowCount - $invalidRowCount -
        $conflictRowCount - $duplicateRowCount - $mismatchCount
      );
    }
    $this->assign('dupeActionString', $dupeActionString);

    $properties = [
      'totalRowCount',
      'validRowCount',
      'invalidRowCount',
      'conflictRowCount',
      'downloadConflictRecordsUrl',
      'downloadErrorRecordsUrl',
      'duplicateRowCount',
      'downloadDuplicateRecordsUrl',
      'downloadMismatchRecordsUrl',
      'groupAdditions',
      'unMatchCount',
    ];
    foreach ($properties as $property) {
      $this->assign($property, $this->get($property));
    }
  }

  /**
   * Returns error count from import queue error file
   */
  private function getInvalidRowCount() {
    $file = CRM_Csvimport_Import_Parser::errorFileName(CRM_Csvimport_Import_Parser::ERROR);
    $linecount = 0;
    $handle = fopen($file, "r");
    while (!feof($handle)) {
      $line = fgets($handle);
      if ($line != '') {
        $linecount++;
      }
    }
    fclose($handle);

    return $linecount - 1; // -1 for header
  }

}

