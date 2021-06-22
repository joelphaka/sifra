<?php


namespace App\Http\Controllers;

use App\Models\User;
use Sifra\Http\Request;
use Sifra\Http\Controller;
use Sifra\Http\Session;
use Sifra\Siorm\Validation\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = null;

        if (!isNullOrEmpty($request->input('q'))) {
            // Remove %
            $q = '%'.str_replace('%', '',$request->input('q')).'%';

            $users = User::all()
                ->where('first_name', 'LIKE', $q)
                ->orWhere('last_name', 'LIKE', $q)
                ->orWhere('email', 'LIKE', $q)
                ->orderDesc('id')
                ->get();

        } else {
            $users = User::all()->orderDesc('id')->get();
        }

        return $this->render('user/index', ['users' => $users]);
    }

    public function create()
    {
        require resource('views/user/create.php');
    }

    public function store(Request $request)
    {
        $validator = Validator::make();

        /* Fluent
        $validator->ruleFor('first_name', 'required')
                    ->withMessage('required', 'First Name is required.')
                  ->ruleFor('last_name', 'required')
                    ->withMessage('required', 'Last Name is required.')
                  ->ruleFor('email', 'required|email|unique:users')
                    ->withMessage('required', 'Email is required.')
                    ->withMessage('email', 'Please enter a valid email address.')
                    ->withMessage('unique', 'A user with this email address already exists.');

        $validator->validate($request);

        OR the code bellow
        */

        $customMessages = [
            'first_name.required' => 'First Name is required.',
            'last_name.required' => 'Last Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'A user with this email address already exists.'
        ];

        $errors = $validator->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
        ], $customMessages);

        
        if (!$errors->isEmpty()) return $this->render('user/create');
        
        $user = User::create($request->only(['first_name', 'last_name', 'email']));

        if ($user) {
            Session::set('_new', $user->id);
        } else {
            echo '<h4>could not the create user</h4>';
            sleep(2);
        }

        header("Location: " . url('/users'));
    }

    public function edit(Request $request, $id) 
    {
        if (!!($user = User::find($id))) {
            return $this->render('user/edit', ['user' => $user]);
        } 
        
        echo '<h4>User not found. </h4>';
        echo "<h4>Return to <a href=".url('/users').">users</a></h4>";
    }    
    
    public function update(Request $request, $id) 
    {
        $user = User::find($id);

        if (!$user) {
            echo '<h4>User not found. </h4>';
            echo "<h4>Return to <a href=".url('/users').">users</a></h4>";
        } else {
            
            $validator = validator();

            // Only validate field if present

            $validator->ruleFor('first_name', 'required', $request->has('first_name'))
                ->withMessage('required', 'First Name is required.');

            $validator->ruleFor('last_name', 'required', $request->has('last_name'))
                ->withMessage('required', 'Last Name is required.');
            
            $validator->ruleFor('email', 'required|email', $request->has('email'))
                ->withMessage('required', 'Email is required.')
                ->withMessage('email', 'Please enter a valid email address.');

            $errors = $validator->validate($request);

            if (!$errors->isEmpty()) {
                return $this->render('user/edit', ['user' => $user]);
            }

            if ($request->has('first_name')) $user->first_name = $request->input('first_name');
            if ($request->has('last_name')) $user->last_name = $request->input('last_name');
            if ($request->has('email')) $user->email = $request->input('email');

            if ($user->save()) {
                Session::set('updated', $user->id);
            }

            header("Location: " . url("/users/edit/$user->id"));
        }
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            echo '<h4>User not found. </h4>';
            echo "<h4>Return to <a href=".url('/users').">users</a></h4>";
        } else {
            if ($user->destroy()) {
                Session::set('deleted', $user->id);

                header("Location: " . url("/users"));
            } else {
                echo '<h4>Could not delete the user. </h4>';
                echo "<h4>Return to <a href=".url('/users').">users</a></h4>";
            }
        }
    }
}