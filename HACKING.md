# Glossary

* Code elements – syntactical parts of the PHP language, like namespaces,
  functions, classes (and contained methods), variables, etc.
* Resources – things like scripts, styles, filters, actions, taxonomies,
  capabilities, etc.
* Elements – code elements and resources.
* Analyzer – gathers structural information about a theme, i.e. produces a
  survey of its elements.
* Renderer – turns data obtained from an element into HTML or terminal output.
* Abstract Syntax Tree (AST) – tree representation of the abstract syntactic
  structure of a source code file.
* Visitor – object with a function that is invoked with every node in a tree
  when traversing it.
* Scanner – runs a list of checks on a theme.
* Check – checks for the presence of a specific error in a file, and reports it.

# Code Walkthrough

* There are currently three *scan types* available: 'Undefined Function Check',
  'WP.com Theme Review', or 'VIP Theme Review', each of which is associated
  with a list of checks and analyzers (cf. Glossary).
* There are two ways to invoke VIP-scanner:
  1. It can be run from the corresponding WordPress admin page (in the 'Tools'
     section). That admin page is added by `class VIP_Scanner_UI` (found in
     `vip-scanner.php` in the plugin's root directory). When the user clicks
     the 'Scan' button, its class method `do_theme_review()` is invoked to
     generate the results for the scan type selected by the user.
  2. It can be run as a WP-CLI command, i.e. `wp vip-scanner`. In this case, a
     `VIPScanner_Command` (vip-scanner/class-wp-cli.php) instance is
     constructed, and depending on the subcommand invoked, either its
     `scan_theme()` or its `analyze_theme()` method is called.
* Either way, an instance of `VIP_Scanner` (vip-scanner/vip-scanner.php) is
  then used to call its `run_theme_review()` method.
  * `VIP_Scanner::run_theme_review()` creates an instance of `ThemeScanner`
    (vip-scanner/scanners/class-theme-scanner.php) for the given review type.
    * `ThemeScanner` extends `DirectoryScanner`, which is used to collect all
      files within the theme's directory for scrutiny.
    * `DirectoryScanner` extends `BaseScanner`, which is at the core of
      VIP-scanner: Upon construction, it groups files by their type (like
      'php', 'css', or 'html', etc.).
      * When its `scan` method is invoked, `run_scanners()` is called.
        `AnalyzedPHPFile` (vip-scanner/class-analyzed-php-file.php) and
        `AnalyzedCSSFile` (vip-scanner/class-analyzed-css-file.php) objects are
        created for the 'php' and 'css' types, respectively.
        * Upon construction, `AnalyzedPHPFile` uses PHP-Parser to parse PHP
          files, yielding an AST, which can be accessed using
          `AnalyzedPHPFile::get_node_tree()`. Most information required by
          analyzers and scanners is readily available from the AST's nodes.
        * To add some more required information to the nodes, the tree is
          traversed, and a number of node visitors are run on each node.
      * The scanner's `run_scanners()` method then iterates over the checks and
        analyzers associated with the selected scan type.
        * Files (names and contents) are passed to the check's `check()` method.
          Any errors found by the checks are obtained via the `get_results()` class
          method inherited from `BaseCheck` (vip-scanner/class-base-check.php),
          and appended to the scanner's public `errors` member, while the number of
          checks performed is added to its `total_checks` member.
        * The pre-analyzed files (i.e. `AnalyzedPHPFile` and `AnalyzedCSSFile`
          objects) are passed to the analyzer's `analyze()` method. The analyzer then
	      creates a set of corresponding *elements* (see below), which are obtained
	      via its `get_elements()` method, and added to the scanner's public
	      `elements` member, while statistics are obtained via `get_stats()` and
	      appended to the `stats` member.
          * `CodeAnalyzer` creates an object of a subclass of `CodeElement` for
            each relevant code element in the AST obtained from
            `AnalyzedPHPFile::get_node_tree()`. The `CodeElement` objects extract
            relevant information from the AST nodes to make it available in a
            form suitable for the renderer, i.e. via their public methods, e.g.
            `get_header()`, `get_child_summary()`, `get_attributes()`,
            `get_stats()`, etc. `CodeElement` objects can be recursive: for
            example, a `ClassCodeElement` can contain any number of
            `MethodCodeElement`s. A `CodeElement`'s children can be obtained via
            its `get_children()` method.
          * `ResourceAnalyzer` creates `ResourceCodeElement`s for 
            WordPress-specific resources (see glossary), which analyze the
            corresponding function calls (such as `add_filter()`,
            `wp_enqueue_script()`, etc.) found in the AST.
          * `ThemeAnalyzer` adds information found in the themes CSS file, such
            as the name of the parent theme (if any).
  * `VIP_Scanner::run_theme_review()` returns the `ThemeScanner` instance.
* The scanner's properties are rendered for display:
  1. In the `VIP_Scanner_UI` class, the `display_theme_review_results()`
     displays the errors found by the checks. It then loops through the
     analyzers' elements, whose data are rendered into HTML by passing them to
     `ElementRenderer` (vip-scanner/renderers/class-element-renderer.php),
     whose `display()` method is eventually called.
  2. In case of the `VIPScanner_Command`, scanner properties are formatted for
     terminal output. In case of the elements, the renderer's `display()`
     method is called with an additional parameter indicating 'bare' (as
     opposed to HTML) output.

# Code Organization

Analyzers, elements, renderers, visitors, scanners, and checks are found in
the eponymous subdirectories of the vip-scanner directory (except for some
stray base classes which are in the vip-scanner directory itself).
