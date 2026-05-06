<?php

namespace App\Http\Controllers;
use App\Models\studentmngt;
use Illuminate\Support\Facades\DB;
use Response;
use Illuminate\Http\Request;
use App\Models\Student;

class studmngt_controller extends Controller
{
    public function index()
    {
        $students = studentmngt::all();
        return view('student.index', compact('students'));
    }

    public function addstud()
    {
        return view('student.addstud');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fname' => 'required|max:255',
            'lname' => 'required|max:255',
            'mname' => 'required|max:255',
            'add' => 'required|max:255',
            'dob' => 'required|date',
        ]);

        studentmngt::create($request->all());

        return redirect()->route('student.index')
                         ->with('status', 'Student Added Successfully!');
    }

    public function edit(int $id)
    {
        $student = studentmngt::findOrFail($id);
        return view('student.edit', compact('student'));
    }

    public function update(Request $request, int $id) {
        $request->validate([
            'fname' => 'required|max:255',
            'lname' => 'required|max:255',
            'mname' => 'required|max:255',
            'add' => 'required|max:255',
            'dob' => 'required|date',
        ]);
    
        studentmngt::findOrFail($id)->update($request->all());
        return redirect()->back()->with('status', 'Student Updated Successfully!');
    }
public function delete(int $id){
        $student = studentmngt::findOrFail($id);
        $student->delete();
        return redirect ()->back()->with('status','Student Deleted');
    }

}
