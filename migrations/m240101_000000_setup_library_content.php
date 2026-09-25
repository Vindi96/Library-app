<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\fields\Assets as AssetsField;
use craft\fields\Date as DateField;
use craft\fields\Dropdown as DropdownField;
use craft\fields\Lightswitch as LightswitchField;
use craft\fields\PlainText;
use craft\fs\Local as LocalFs;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Volume;
use yii\base\Exception;

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

        // ---- Asset volume -------------------------------------------

        $fs = new LocalFs([
            'name' => 'Library Media',
            'handle' => 'libraryMedia',
            'path' => '@webroot/uploads',
            'hasUrls' => true,
            'url' => '@web/uploads',
        ]);
        $this->check(Craft::$app->getFs()->saveFilesystem($fs), $fs);

        $volume = new Volume([
            'name' => 'Library Media',
            'handle' => 'libraryMedia',
            'fsHandle' => $fs->handle,
        ]);
        $this->check(Craft::$app->getVolumes()->saveVolume($volume), $volume);
        $volumeSource = "volume:$volume->uid";

        // ---- Fields -------------------------------------------------

        $author = new PlainText(['name' => 'Author', 'handle' => 'bookAuthor']);
        $this->check($fieldsService->saveField($author), $author);

        $isbn = new PlainText(['name' => 'ISBN', 'handle' => 'isbn']);
        $this->check($fieldsService->saveField($isbn), $isbn);

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
        $this->check($fieldsService->saveField($genre), $genre);

        $available = new LightswitchField(['name' => 'Available', 'handle' => 'available', 'default' => true]);
        $this->check($fieldsService->saveField($available), $available);

        $coverImage = new AssetsField([
            'name' => 'Cover Image',
            'handle' => 'coverImage',
            'sources' => [$volumeSource],
            'defaultUploadLocationSource' => $volumeSource,
            'maxRelations' => 1,
        ]);
        $this->check($fieldsService->saveField($coverImage), $coverImage);

        $summary = new PlainText(['name' => 'Summary', 'handle' => 'summary', 'multiline' => true]);
        $this->check($fieldsService->saveField($summary), $summary);

        $eventDate = new DateField(['name' => 'Event Date', 'handle' => 'eventDate', 'showTimeZone' => false]);
        $this->check($fieldsService->saveField($eventDate), $eventDate);

        $eventLocation = new PlainText(['name' => 'Location', 'handle' => 'eventLocation']);
        $this->check($fieldsService->saveField($eventLocation), $eventLocation);

        $registrationRequired = new LightswitchField([
            'name' => 'Registration Required',
            'handle' => 'registrationRequired',
        ]);
        $this->check($fieldsService->saveField($registrationRequired), $registrationRequired);

        $jobTitle = new PlainText(['name' => 'Job Title', 'handle' => 'jobTitle']);
        $this->check($fieldsService->saveField($jobTitle), $jobTitle);

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
        $this->check($fieldsService->saveField($department), $department);

        $photo = new AssetsField([
            'name' => 'Photo',
            'handle' => 'photo',
            'sources' => [$volumeSource],
            'defaultUploadLocationSource' => $volumeSource,
            'maxRelations' => 1,
        ]);
        $this->check($fieldsService->saveField($photo), $photo);

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

    /**
     * Throws if a save failed, so the migration never half-succeeds silently.
     */
    private function check(bool $saved, object $model): void
    {
        if (!$saved) {
            $errors = method_exists($model, 'getErrorSummary') ? $model->getErrorSummary(true) : [];
            throw new Exception(sprintf('Could not save %s: %s', $model::class, implode(' ', $errors)));
        }
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
        ]);
        $entryType->setFieldLayout($fieldLayout);
        $this->check($entriesService->saveEntryType($entryType), $entryType);

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
        $this->check($entriesService->saveSection($section), $section);
    }
}
