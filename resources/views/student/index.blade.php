@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Student information') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

        <div class="row">


                <div class="card-head">

                </div>

                <div class="card-body">



                    <table class="table table-bordered table-stiped fs-1 text-black">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Middle Name</th>
                                <th>Address</th>
                                <th>Date of Birth</th>
                                <th><a href="{{ route('student.addstud') }}" class="btn btn-success">Delete</a></th>
    </div>
                            </tr>
                        </thead>

                        <tbody>
    @foreach ($students as $student)
        <tr>
            <td>{{ $student->id }}</td>
            <td>{{ $student->fname }}</td>
            <td>{{ $student->lname }}</td>
            <td>{{ $student->mname }}</td>
            <td>{{ $student->add }}</td>
            <td>{{ $student->dob }}</td>
            <td>
                <a href="{{ route('student.edit', $student->id) }}" class="btn btn-success btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <form action="{{ route('student.delete', $student->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?')"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
</tbody>


                    </table>
                </div>


                <div class="card-footer">

                </div>


            <!-- /.row -->
        </div><!-- /.container-fluid -->
        <a href="{{ route('student.addstud') }}" class="btn btn-success">Add New Student</a>
    </div>
    <!-- /.content -->
@endsection
