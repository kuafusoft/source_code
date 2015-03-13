<p>Link the item to the following projects:</p>
<table>
<tbody>
<?php
    $i = 0;
    print_r("<tr>");
    foreach($this->projects as $project){
        $checked = 'checked="checked"';
        $color = 'style="color:red"';
        if (empty($project['linked'])){
            $checked = '';    
            $color = '';
        }
        print_r('<td '.$color.'><input type="checkbox" name="projects" '.$checked.' value="'.$project['id'].'"/>'.$project['name'].'</td>');
        $i ++;
        if ($i % 4 == 0){
            print_r("</tr>");
        }
        
    }
    if ($i % 4)
        print_r("</tr>");    
?>
</tbody>
</table>