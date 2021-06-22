<h3><?=$user->first_name.' '.$user->last_name?></h3></h3>

<?php if (sessionHas('updated')):?>
    <span style="color:green">Details updated</span>
    <?php sessionDelete('updated') ?>
<?php endif;?>

<form action="<?=url("/users/edit/$user->id")?>" method="post">
    <label for="first_name">First Name</label><br>
    <input type="text" name="first_name" id="first_name" 
        value="<?=request()->has('first_name') ? request('first_name') : $user->first_name?>">
    <?php if (errors()->has('first_name')):?>
        <span style="color:red"><?=errors()->first('first_name')?></span>
    <?php endif;?>
    <br>

    <label for="last_name">Last Name</label><br>
    <input type="text" name="last_name" id="last_name" 
        value="<?=request()->has('last_name') ? request('last_name') : $user->last_name?>">
    <?php if (errors()->has('last_name')):?>
        <span style="color:red"><?=errors()->first('last_name')?></span>
    <?php endif;?>
    <br>

    <label for="email">Email</label><br>
    <input type="email" name="email" id="email" 
        value="<?=request()->has('email') ? request('email') : $user->email?>">
    <?php if (errors()->has('email')):?>
        <span style="color:red"><?=errors()->first('email')?></span>
    <?php endif;?>
    <br>

    <input type="hidden" name="_csrf" value="<?=csrf()?>"><br>
    <div>
        <button type="submit">Save</button>
        <button type="button"
            onclick="event.preventDefault(); if (window.confirm('Are you sure?')){document.getElementById('deleteForm').submit();}">
            Delete
        </button>
    </div>
</form>
<form id="deleteForm" action="<?=url("/users/delete/$user->id")?>" method="post">
    <input type="hidden" name="_csrf" value="<?=csrf()?>"/>
</form>

<a href="/users">See users</a>