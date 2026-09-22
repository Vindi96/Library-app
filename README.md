# Library Craft CMS Site

A [Craft CMS](https://craftcms.com) project scaffold for a public library website:
book catalog, events/programs calendar, staff directory, and general content pages.

> **Note:** This repo has the full project structure (config, content-model migration,
> Twig templates, CSS) but does **not** include the `vendor/` folder — Craft itself is
> pulled in via Composer, which needs network access to Packagist. Run the setup below
> on your own machine to finish the install.

## Content model

| Section | Type    | Fields |
|---------|---------|--------|
| Books   | Channel | Author, ISBN, Genre (dropdown), Available (toggle), Cover Image, Summary |
| Events  | Channel | Event Date, Location, Registration Required (toggle), Summary |
| Staff   | Channel | Job Title, Department (dropdown), Photo |
| Pages   | Structure | Summary (used for hours, about, policies, etc.) |

The content model is created automatically by the migration in
`migrations/m240101_000000_setup_library_content.php` — no manual field setup in the
control panel required.

## Setup

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   Fill in `CRAFT_SECURITY_KEY` (generate with `php craft setup/security-key`) and your
   database credentials.

3. **Create the database**, then run Craft's install wizard:
   ```bash
   php craft setup
   ```

4. **Create the asset volume** used by the content model (`library-media`) in the
   control panel under Settings → Assets, or via a filesystem/volume migration.

5. **Run the content migration** to create the Books / Events / Staff / Pages sections
   and their fields:
   ```bash
   php craft migrate/up --type=content
   ```

6. **Serve the site**
   ```bash
   php craft serve
   ```
   or point your local web server's document root at `/web`.

7. Log into the control panel at `/admin` and start adding books, events, and staff
   entries.

## Structure

```
config/           General & DB config
migrations/       Content-model migration (sections, fields, entry types)
templates/        Twig front-end templates
web/              Public web root (index.php, compiled CSS)
craft             Console entry point
composer.json     Requires craftcms/cms
```

## Next steps

- Add real volume/filesystem config for asset uploads (local or S3).
- Wire up a search/filter UI on `/books` beyond the basic query params included here.
- Add pagination styling and a proper 404/error template.
- Consider Craft's Commerce or a holds/reservations plugin if patrons need to place
  holds on books directly from the site.
