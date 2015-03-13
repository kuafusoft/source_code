<?php 
    $totalComments = count($this->comments);
    if($totalComments){
?>
<fieldset>
    <legend>Comments(Total <?php echo $totalComments; ?>)</legend>
    <div id='srs_comments'>
    <?php 
        $count = 0;
        $max = 5;
        foreach($this->comments as $comment){
            $count ++;
            print_r('<div id="srs_comment_'.$comment['id'].'" class="srs_comment_item">');
            print_r($comment['commentator']." at ".$comment['created'].":");
            print_r('<div class="srs_comment_content">'.$comment['comment'].'</div>');
            print_r("<BR />");
            if ($count > $max){
                // create a MORE button
                print_r('<a href="javascript:srs_comment_more('.$comment['id'].')">More</a>');
                break;
            }
        }
    ?>
    </div>
</fieldset>
<?php
    }
?>
<fieldset>
    <legend>Add New Comment</legend>
    <textarea id="comment_new" rows="5" cols="80">New Comment</textarea>
</fieldset>

