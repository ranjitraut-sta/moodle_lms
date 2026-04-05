<?Php 
namespace theme_mytheme\lesson;

class LessonFactory {

    public static function create($modtype, $cm, $DB, $context, $cmid) {

        return match ($modtype) {
            'page'        => new Page($cm, $DB),
            'resource'    => new Resource($cm, $DB, $context),
            'url'         => new Url($cm, $DB),
            'quiz'        => new Quiz($cmid),
            'book'        => new Book($cm, $DB),
            'scorm'       => new SCORM($cm, $DB, $cmid),
            'forum'       => new Forum($cm, $DB, $cmid),
            'folder'      => new Folder($cm, $DB, $context),
            'h5pactivity' => new H5P($cm, $DB, $context, $cmid),
            'hvp'         => new H5P($cm, $DB, $context, $cmid),
            'customcert'  => new CustomCert($cm, $DB, $cmid),
            'label'       => new Label($cm, $DB),
            default       => new DefaultModule($cm),
        };
    }
}