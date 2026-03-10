<div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="card border-0 shadow-lg p-4" style="max-width: 500px; width: 100%; border-radius: 1.25rem;">
        
        <div class="card-body">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark">Create Account</h2>
                <p class="text-muted small">Join our global community of students today.</p>
            </div>

            <form>
                <div class="row g-2 mb-3">
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="firstName" placeholder="First Name">
                            <label for="firstName">First Name</label>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="lastName" placeholder="Last Name">
                            <label for="lastName">Last Name</label>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md">
                        <div class="form-floating">
                            <select class="form-select" id="schoolSelect">
                                <option selected>Open to select</option>
                                <option value="1">High School</option>
                                <option value="2">University</option>
                            </select>
                            <label for="schoolSelect">Your School</label>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                            <select class="form-select" id="gradeSelect">
                                <option selected>Open to select</option>
                                <option value="1">Freshman</option>
                                <option value="2">Senior</option>
                            </select>
                            <label for="gradeSelect">Your Grade</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" placeholder="name@example.com">
                    <label for="email">Email address</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" placeholder="Password">
                    <label for="password">Password</label>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold" style="border-radius: 0.75rem;">
                        Sign Up
                    </button>
                </div>

                <div class="text-center">
                    <p class="small text-muted mb-0">Already have an account? 
                        <a href="#" class="text-decoration-none fw-semibold">Log in</a>
                    </p>
                </div>
            </form>
        </div>

    </div>
</div>