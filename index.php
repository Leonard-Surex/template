<?php
    require_once('vendor/autoload.php');

    use Template\Template;

    //$arr = Template::render("{{vars.b}}{{block header}}{{vars.a}}{{vars[0]}}{{end block}}Test Test{{show header}}", ['vars'=>['a'=>'Leonard','b'=>'Audrey']]);

    $arr = [
        'users' => [
            ['name' => 'Leonard Waugh', 'title' => 'Software Engineer', 'description' => 'The developer of this template engine.'],
            ['name' => 'Audrey Waugh', 'title' => 'Retired', 'description' => 'Payed to breath.'],
            ['name' => 'Anthony Harle', 'title' => 'Artist', 'description' => 'Loves penises.'],
        ],
        'number' => 4,
    ];

    $template = new Template();
    echo $template->renderFrom("demo.html", $arr);
    //echo $template->block("secret");


    //echo Template::renderFrom("demo.html", $arr);