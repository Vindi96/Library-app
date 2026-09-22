<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\fields\Assets as AssetsField;
use craft\fields\Date as DateField;
use craft\fields\Dropdown as DropdownField;
use craft\fields\Lightswitch as LightswitchField;
use craft\fields\PlainText;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\Section;
use craft\models\Section_SiteSettings;

/**
 * Sets up the content model for a public library website:
 *   - Books (catalog)
 *   - Events (programs / classes / story time)
 *   - Staff (directory)
 *   - Pages (about, hours, policies, etc.)
 */
class m240101_000000_setup_library_content extends Migration
{
    public function safeUp(): bool
    {
        $fieldsService = Craft::$app->getFields();
        $entriesService = Craft::$app->getEntries();

        // ---- Fields -------------------------------------------------

        $author = new PlainText(['name' => 'Author', 'handle' => 'author']);
        $fieldsService->saveField($author);

        $isbn = new PlainText(['name' => 'ISBN', 'handle' => 'isbn']);
        $fieldsService->saveField($isbn);

        $genre = new DropdownField([
            'name' => 'Genre',
            'handle' => 'genre',
            'options' => [
                ['label' => 'Fiction', 'value' => 'fiction'],
                ['label' => 'Non-Fiction', 'value' => 'nonfiction'],
                ['label' => "Children's", 'value' => 'childrens'],
                ['label' => 'Young Adult', 'value' => 'ya'],
                ['label' => 'Reference', 'value' => 'reference'],
                ['label' => 'Periodical', 'value' => 'periodical'],
            ],
        ]);
        $fieldsService->saveField($genre);

        $available = new LightswitchField(['name' => 'Available', 'handle' => 'available', 'default' => true]);
        $fieldsService->saveField($available);

        $coverImage = new AssetsField([
            'name' => 'Cover Image',
            'handle' => 'coverImage',
            'singleUploadLocationSource' => 'volume:library-media',
            'maxRelations' => 1,
        ]);
        $fieldsService->saveField($coverImage);

        $summary = new PlainText(['name' => 'Summary', 'handle' => 'summary', 'multiline' => true]);
        $fieldsService->saveField($summary);

        $eventDate = new DateField(['name' => 'Event Date', 'handle' => 'eventDate', 'showTimeZone' => false]);
        $fieldsService->saveField($eventDate);

        $eventLocation = new PlainText(['name' => 'Location', 'handle' => 'eventLocation']);
        $fieldsService->saveField($eventLocation);

        $registrationRequired = new LightswitchField([
            'name' => 'Registration Required',
            'handle' => 'registrationRequired',
        ]);
        $fieldsService->saveField($registrationRequired);

        $jobTitle = new PlainText(['name' => 'Job Title', 'handle' => 'jobTitle']);
        $fieldsService->saveField($jobTitle);

        $department = new DropdownField([
            'name' => 'Department',
            'handle' => 'department',
            'options' => [
                ['label' => 'Circulation', 'value' => 'circulation'],
                ['label' => 'Reference', 'value' => 'reference'],
                ['label' => "Children's Services", 'value' => 'childrens'],
                ['label' => 'Administration', 'value' => 'admin'],
                ['label' => 'IT', 'value' => 'it'],
            ],
        ]);
        $fieldsService->saveField($department);

        $photo = new AssetsField([
            'name' => 'Photo',
            'handle' => 'photo',
            'singleUploadLocationSource' => 'volume:library-media',
            'maxRelations' => 1,
        ]);
        $fieldsService->saveField($photo);

        // ---- Sections -------------------------------------------------

        $this->createSection(
            $entriesService,
            'Books',
            'books',
            Section::TYPE_CHANNEL,
            'books/{slug}',
            'books/_entry',
            [$author, $isbn, $genre, $available, $coverImage, $summary]
        );

        $this->createSection(
            $entriesService,
            'Events',
            'events',
            Section::TYPE_CHANNEL,
            'events/{slug}',
            'events/_entry',
            [$eventDate, $eventLocation, $registrationRequired, $summary]
        );

        $this->createSection(
            $entriesService,
            'Staff',
            'staff',
            Section::TYPE_CHANNEL,
            'staff/{slug}',
            'staff/_entry',
            [$jobTitle, $department, $photo]
        );

        $this->createSection(
            $entriesService,
            'Pages',
            'pages',
            Section::TYPE_STRUCTURE,
            '{slug}',
            'pages/_entry',
            [$summary]
        );

        return true;
    }

    public function safeDown(): bool
    {
        echo "m240101_000000_setup_library_content cannot be reverted automatically.\n";
        return false;
    }

    private function createSection(
        $entriesService,
        string $name,
        string $handle,
        string $type,
        string $uriFormat,
        string $template,
        array $fields
    ): void {
        $fieldLayout = new FieldLayout(['type' => \craft\elements\Entry::class]);
        $tab = new \craft\models\FieldLayoutTab(['name' => 'Content', 'layout' => $fieldLayout]);
        $tab->setElements(array_map(
            fn($field) => new \craft\fieldlayoutelements\CustomField($field),
            $fields
        ));
        $fieldLayout->setTabs([$tab]);

        $entryType = new EntryType([
            'name' => $name,
            'handle' => $handle,
            'hasTitleField' => true,
            'titleTranslationMethod' => \craft\models\EntryType::TITLE_TRANSLATION_METHOD_SITE,
        ]);
        $entryType->setFieldLayout($fieldLayout);
        $entriesService->saveEntryType($entryType);

        $section = new Section([
            'name' => $name,
            'handle' => $handle,
            'type' => $type,
            'siteSettings' => [
                new Section_SiteSettings([
                    'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => $uriFormat,
                    'template' => $template,
                ]),
            ],
        ]);
        $section->setEntryTypes([$entryType]);
        $entriesService->saveSection($section);
    }
}
