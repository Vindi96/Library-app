<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\fieldlayoutelements\entries\EntryTitleField;
use yii\base\Exception;

/**
 * Adds the Title field to the library entry types' layouts. The setup migration originally
 * left it out, which made Craft 5 treat the entry types as title-less and discard titles.
 * Safe to run on installs that already have it.
 */
class m261013_000000_add_entry_title_fields extends Migration
{
    public function safeUp(): bool
    {
        $entriesService = Craft::$app->getEntries();

        foreach (['books', 'events', 'staff', 'pages'] as $handle) {
            $entryType = $entriesService->getEntryTypeByHandle($handle);
            if (!$entryType) {
                continue;
            }

            $fieldLayout = $entryType->getFieldLayout();
            if ($fieldLayout->isFieldIncluded('title')) {
                continue;
            }

            $tab = $fieldLayout->getTabs()[0];
            $tab->setElements([new EntryTitleField(), ...$tab->getElements()]);
            $fieldLayout->setTabs($fieldLayout->getTabs());

            if (!$entriesService->saveEntryType($entryType)) {
                throw new Exception("Could not save entry type $handle: " . implode(' ', $entryType->getErrorSummary(true)));
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m261013_000000_add_entry_title_fields cannot be reverted.\n";
        return false;
    }
}
