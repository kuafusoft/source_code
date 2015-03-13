支付账户:<?php echo $this->zjzh['name'];?><BR />
账号:<?php echo $this->zjzh['account_no'];?><BR />
帐户余额:<span id='remained'><?php echo $this->zjzh['remained'];?></span><BR />

支付给:<select id='hzhb_id'>
    <?php
        foreach($this->hzbh as $hzhb){
            echo '<option value="'.$hzhb['id'].'">'.$hzhb['name'].'(应付款'.$hzhb['total_money'].')</option>'; 
        }
    ?>
</select> <BR />
支付原因：:<select id='zjwl_fl_id'>
    <?php
        foreach($this->zjwl_fl as $zjwl_fl){
            echo '<option value="'.$zjwl_fl['id'].'">'.$zjwl_fl['name'].'</option>'; 
        }
    ?>
</select> 
<div><label for='amount'>支付金额:</label><input type='text' id='amount' /></div>
