<?php
if ($_SERVER['REQUEST_URI'] === '/Test/json-ld-test-suite/remote-doc-0005-in.jsonld') {
    header('Location: http://localhost:8080/Test/json-ld-test-suite/remote-doc-0001-in.jsonld');
    http_response_code(301);
    return;
} elseif ($_SERVER['REQUEST_URI'] === '/Test/json-ld-test-suite/remote-doc-0006-in.jsonld') {
    header('Location: http://localhost:8080/Test/json-ld-test-suite/remote-doc-0001-in.jsonld');
    http_response_code(303);
    return;
} elseif ($_SERVER['REQUEST_URI'] === '/Test/json-ld-test-suite/remote-doc-0007-in.jsonld') {
    header('Location: http://localhost:8080/Test/json-ld-test-suite/remote-doc-0001-in.jsonld');
    http_response_code(307);
    return;
}


if ($_SERVER['REQUEST_URI'] === '/Test/json-ld-test-suite/remote-doc-0009-in.jsonld') {
    header('Link: <remote-doc-0009-context.jsonld>; rel="http://www.w3.org/ns/json-ld#context"');
} elseif ($_SERVER['REQUEST_URI'] === '/Test/json-ld-test-suite/remote-doc-0010-in.json') {
    header('Link: <remote-doc-0010-context.jsonld>; rel="http://www.w3.org/ns/json-ld#context"');
} elseif ($_SERVER['REQUEST_URI'] === '/Test/json-ld-test-suite/remote-doc-0011-in.jldt') {
    header('Link: <remote-doc-0011-context.jsonld>; rel="http://www.w3.org/ns/json-ld#context"');
} elseif ($_SERVER['REQUEST_URI'] === '/Test/json-ld-test-suite/remote-doc-0012-in.json') {
    header('Link: <remote-doc-0012-context1.jsonld>; rel="http://www.w3.org/ns/json-ld#context"');
    header('Link: <remote-doc-0012-context2.jsonld>; rel="http://www.w3.org/ns/json-ld#context"', false);
}

if (pathinfo($_SERVER['SCRIPT_FILENAME'])['extension'] === 'jsonld') {
    header('Content-Type: application/ld+json');
    readfile($_SERVER['SCRIPT_FILENAME']);
} elseif (pathinfo($_SERVER['SCRIPT_FILENAME'])['extension'] === 'json') {
    header('Content-Type: application/json');
    readfile($_SERVER['SCRIPT_FILENAME']);
} elseif (pathinfo($_SERVER['SCRIPT_FILENAME'])['extension'] === 'jldt') {
    header('Content-Type: application/jldTest+json');
    readfile($_SERVER['SCRIPT_FILENAME']);
} elseif (pathinfo($_SERVER['SCRIPT_FILENAME'])['extension'] === 'jldte') {
    header('Content-Type: application/jldTest');
    readfile($_SERVER['SCRIPT_FILENAME']);
} else {
    return false;
}
