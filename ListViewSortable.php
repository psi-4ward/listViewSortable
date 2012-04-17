<?php if(!defined('TL_ROOT')) {die('You cannot access this file directly!');
}

/**
 * @copyright 4ward.media 2012 <http://www.4wardmedia.de>
 * @author Christoph Wiechert <wio@psitrax.de>
 */


class ListViewSortable extends System
{
	public function injectJavascript($table)
	{

		if($GLOBALS['TL_DCA'][$table]['list']['sorting']['listViewSortable'] && !$this->Input->get('act') && !isset($GLOBALS['listViewSortable_inserted']))
		{

			$GLOBALS['TL_DCA'][$table]['list']['sorting']['flag'] = 7;
			$GLOBALS['TL_DCA'][$table]['list']['sorting']['mode'] = 1;
			$GLOBALS['TL_DCA'][$table]['list']['sorting']['disableGrouping'] = true;


			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/listViewSortable/html/listViewSortable.js';
		}

		// we save the first call of this function, cause this is the "main" table
		// later on, if a child or parent table gets loaded we dont want to include the scripts
		$GLOBALS['listViewSortable_inserted'] = true;
	}

	public function resort($strAction, DataContainer $dc)
	{

	    if ($strAction == 'listViewSortable')
	    {
			$this->import('Database');

			if(!preg_match("~^\d+$~",$this->Input->post('id')))
			{
				die('Error: erroneous id');
			}

			// ID is set (insert after the current record)
			if ($this->Input->post('afterid'))
			{
				$objCurrentRecord = $this->Database->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
												   ->limit(1)
												   ->executeUncached($this->Input->post('afterid'));

				// Select current record
				if ($objCurrentRecord->numRows)
				{
					$curSorting = $objCurrentRecord->sorting;

					$objNextSorting = $this->Database->prepare("SELECT MIN(sorting) AS sorting FROM " . $dc->table . " WHERE sorting>?")
													 ->executeUncached($curSorting);

					// Select sorting value of the next record
					if ($objNextSorting->numRows)
					{
						$nxtSorting = $objNextSorting->sorting;

						// Resort if the new sorting value is no integer or bigger than a MySQL integer field
						if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
						{
							$count = 1;

							$objNewSorting = $this->Database->executeUncached("SELECT id, sorting FROM " . $dc->table . " ORDER BY sorting");

							while ($objNewSorting->next())
							{
								$qry = $this->Database->prepare("UPDATE " . $dc->table . " SET sorting=? WHERE id=?")
											   ->execute(($count++*128), $objNewSorting->id);
								if ($objNewSorting->sorting == $curSorting)
								{
									$newSorting = ($count++*128);
								}
							}
						}

						// Else new sorting = (current sorting + next sorting) / 2
						else $newSorting = (($curSorting + $nxtSorting) / 2);
					}

					// Else new sorting = (current sorting + 128)
					else $newSorting = ($curSorting + 128);

					// Set new sorting
					$qry = $this->Database->prepare("UPDATE " . $dc->table . " SET sorting=? WHERE id=?")
								->execute($newSorting,$this->Input->post('id'));
				}

				// ID is not set (insert at the end)
				else
				{
					$objNextSorting = $this->Database->executeUncached("SELECT MAX(sorting) AS sorting FROM " . $this->strTable);

					if ($objNextSorting->numRows)
					{
						$qry = $this->Database->prepare("UPDATE " . $dc->table . " SET sorting=? WHERE id=?")
										->execute(intval($objNextSorting->sorting + 128),$this->Input->post('id'));
					}
				}

			}
	    }
	}
}
