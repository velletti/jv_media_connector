<?php
Header('Content-Type: application/json; charset=utf-8') ;

// echo json_encode( array("test" => 1 )  ) ;
echo json_encode( $_FILES['file'] ) ;
