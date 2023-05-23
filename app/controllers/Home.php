<?php
// This is a controller
class Home extends Controller
{
    public function index($name='')
    {
        //
        /*
        $user = $this->model('User');
        echo 'This is name' + $name;
        $user->name = $name;

        $this->view('home.view',['name'=>$user->name]);;  */
        echo 'ffffffffffff';
        echo 'naaaammmmeee' . $name;
    }
}

