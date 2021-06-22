<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users</title>
    <style>
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        table th,
        table td {
            border: 1px solid black !important;
            text-align: left;
            padding: 3px;
        }
    </style>
</head>
<body>
    <div>
        <a href="<?=url('/users/create')?>">Create a user</a>
        <?php if (count($users)):?>
            <a href="<?=url('/users')?>">Refresh</a>
        <?php endif;?>
    </div>
    <br>
    <?php if (sessionHas('_new')):?>
        <span>New used added</span>
    <?php endif;?>    
    <?php if (sessionHas('deleted')):?>
        <span>The user was deleted</span>
    <?php endif;?>
    
    <?php if (count($users)):?>
        <form action="<?=url('/users')?>" method="get">
            <Label name="q">Search:</Label>
            <input id="q" name="q" type="text" 
                placeholder="Search by First name, Last name or Email"
                style="width:300px;padding:6px;margin:10px;">
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th></th>
                <th></th>
            </tr>
            <?php foreach ($users as $user):?>
                <tr style="font-weight: <?=sessionGet('_new')==$user->id ? 'bold': 'normal'?>">
                    <td><?=$user->id?></td>
                    <td><?=$user->first_name.' '.$user->last_name?></td>
                    <td><?=$user->email?></td>
                    <td>
                        <a href="<?=url("/users/edit/$user->id")?>">Edit</a>
                    </td>
                    <td>
                        <?php $formId = "delete-$user->id--form--$user->id"; ?>
                        <a href="#" 
                            onclick="event.preventDefault(); if (window.confirm('Are you sure?')){document.getElementById('<?=$formId?>').submit();}">
                            Delete
                        </a>
                        <form id="<?=$formId?>" action="<?=url("/users/delete/$user->id")?>" method="post">
                            <input type="hidden" name="_csrf" value="<?=csrf()?>"/>
                        </form>
                    </td>
                </tr>
            <?php endforeach;?>
        </table>
    <?php else:?>
        <h4>You have no users.</h4>
    <?php endif;?>
    <?php sessionDelete('_new');?>
    <?php sessionDelete('deleted');?>
</body>
</html>