<?php
	echo $this->strTime;
?>
<div id="srs_information_tabs">
	<ul>
		<li><a href="#tabs-current">Current</a></li>
		<li><a href="#tabs-history">History</a></li>
		<li><a href="#tabs-comment">Comment</a></li>
<!--
		<li><a href="#tabs-test">Testcases&Result</a></li>
-->		
	</ul>
	<div id="tabs-current">
	    <p>Category:<?php echo $this->category; ?></p>
	    <p>Code:<?php echo $this->code;?></p>
	    <p>Content:<?php echo $this->content; ?></p>
	</div>
	<div id="tabs-comment">
	   <?php
            $columns = array('commentator', 'comment', 'created');
        ?>
        <table id="review" border="1" style="width:98%; border:1px solid #cccc; border-collapse:collapse;">
            <thead>
                <tr>
        <?php
            foreach($columns as $column)
                print_r("<th>".ucwords($column)."</th>");
        ?>    
                </tr>
            </thead>
            <tbody>
        <?php 
            foreach($this->comment as $row){
                print_r('<tr>');
                foreach($columns as $column){
                    print_r('<td style="word-break:break-all; word-wrap:break-all;">'.$row[$column].'</td>');
                }
                print_r('</tr>');
            }
        ?>
            </tbody>
        </table>
    </div>
	<div id="tabs-history">
	   <?php
            $columns = array('project', 'category', 'code', 'content', 'link_status', 'history_updated');
        ?>
        <table id="history" border="1" style="width:98%; border:1px solid #cccc; border-collapse:collapse;">
            <thead>
                <tr>
        <?php
            foreach($columns as $column)
                print_r("<th>".ucwords($column)."</th>");
        ?>    
                </tr>
            </thead>
            <tbody>
        <?php 
            foreach($this->history as $row){
                print_r('<tr>');
                foreach($columns as $column){
                    print_r('<td style="word-break:break-all; word-wrap:break-all;">'.$row[$column].'</td>');
                }
                print_r('</tr>');
            }
        ?>
            </tbody>
        </table>
	</div>
<!--	
	<div id='tabs-test'>
	   <?php
            $columns = array('testcaseid', 'name', 'module');
        ?>
        <table id="review" border="1" style="width:98%; border:1px solid #cccc; border-collapse:collapse;">
            <thead>
                <tr>
        <?php
            foreach($columns as $column)
                print_r("<th>".ucwords($column)."</th>");
        ?>    
                </tr>
            </thead>
            <tbody>
        <?php 
            foreach($this->testcase as $row){
                print_r('<tr>');
                foreach($columns as $column){
                    print_r('<td style="word-break:break-all; word-wrap:break-all;">'.$row[$column].'</td>');
                }
                print_r('</tr>');
            }
        ?>
            </tbody>
        </table>
	</div>
-->	
</div>

