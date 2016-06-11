<?php
/**
 * @file
 * An include file that is expecting the following vars
 *
 * - $info
 *   Keyed by filename (no path, no extension)
 *   Values are arrays with keys:
 *   - parent
 *   - weight
 *   - title
 * - $json_file string The output path of the json file.
 *
 * @see  files_to_json.php
 * @see  ini_to_json.php
 */

if (!empty($json_file)) {

  $json = array(
    'chapters' => array(),
    'sections' => array(),
    'settings' => array(),
  );

  $json['settings']['search'] = is_file(($f = $source_dir . '/search--results.md')) ? 'tipuesearch' : NULL;
  if (empty(file_get_contents($f))) {
    echo "search--results.md may not be an empty file; include at least a title";
  }  

  $chapter_sort = 0;
  $section_sort = 0;
  foreach ($info as $file => $data) {
    if (empty($data['title'])) {
      continue;
    }
    
    $chapter = NULL;
    $section = array(
      'id' => clean_id($file),
      'title' => $data['title'],
      'file' => isset($data['file']) ? $data['file'] : $file . get_md_source_file_extension($file),
      'sort' => isset($data['weight']) ? $data['weight'] : $section_sort++,
    );
    $section_sort = max($section['sort'], $section_sort);

    if (!empty($data['parent'])) {
      $chapter = array(
        'id' => clean_id($data['parent']),
        'title' => clean_title($data['parent']),
        'sort' => $chapter_sort++,
      );
      $section['chapter'] = $chapter['id'];
    }
    $json['sections'][] = $section;
    if ($chapter) {
      $json['chapters'][] = $chapter;
    }  
  }
  

  // Write out the json file.
  file_put_contents($json_file, json_encode($json, JSON_PRETTY_PRINT));
}
else {
  echo "Missing \$json_file.";
}