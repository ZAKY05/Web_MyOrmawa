<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0 pb-1">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <div class="mb-5">
                        <h5 class="fw-bold text-gradient-secondary mb-2" id="loginModalLabel">Login Admin</h5>
                        <p class="text-muted mb-0">Masuk ke dashboard administrasi MyOrmawa</p>
                    </div>
                    
                    <div id="loginAlert" class="alert alert-danger d-none" role="alert">
                        Email atau password salah!
                    </div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" 
                                    class="form-control form-control-lg" 
                                    name="email" 
                                    placeholder="admin@myormawa.com" 
                                    value="admin@myormawa.com" 
                                    required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="position-relative">
                                <input type="password" 
                                        class="form-control form-control-lg pe-5" 
                                        name="password" 
                                        placeholder="Masukkan password" 
                                        value="admin123" 
                                        required 
                                        id="passwordInput">
                                <button class="btn position-absolute top-50 end-0 translate-middle-y me-2" 
                                        type="button" 
                                        id="togglePassword"
                                        style="border: none; background: transparent; color: #6c757d;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gradient-primary btn-lg w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="text-muted small">
                            Sistem ini khusus untuk administrator organisasi mahasiswa.<br>
                            Hubungi IT Support jika mengalami kesulitan login.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
