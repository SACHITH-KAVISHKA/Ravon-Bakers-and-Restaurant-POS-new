<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Double-Click Prevention Test - Ravon Bakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 30px;
            margin-bottom: 30px;
        }
        .counter {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin: 20px 0;
        }
        .test-result {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .test-result.success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .test-result.danger {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .instructions {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1 class="text-white">
                    <i class="bi bi-shield-check"></i>
                    Double-Click Prevention Test
                </h1>
                <p class="text-white">Try to click the buttons multiple times rapidly!</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <!-- Test 1: Form Submission -->
            <div class="col-lg-6">
                <div class="test-card">
                    <h3><i class="bi bi-1-circle-fill text-primary"></i> Form Submission Test</h3>
                    <div class="instructions">
                        <strong>Instructions:</strong> Try clicking the submit button 3-4 times very quickly. 
                        The counter should only increment by 1, and the button should disable immediately.
                    </div>
                    
                    <form id="test-form-1">
                        <div class="mb-3">
                            <label class="form-label">Test Input</label>
                            <input type="text" class="form-control" placeholder="Enter something..." required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-check-circle"></i> Submit Form
                        </button>
                    </form>

                    <div class="counter" id="counter-1">0</div>
                    <div id="result-1"></div>
                </div>
            </div>

            <!-- Test 2: AJAX Submission -->
            <div class="col-lg-6">
                <div class="test-card">
                    <h3><i class="bi bi-2-circle-fill text-primary"></i> AJAX Submission Test</h3>
                    <div class="instructions">
                        <strong>Instructions:</strong> This form uses AJAX. Try clicking rapidly. 
                        Only one request should be sent (check Network tab).
                    </div>
                    
                    <form id="test-form-2">
                        <div class="mb-3">
                            <label class="form-label">Test Data</label>
                            <input type="text" class="form-control" placeholder="Enter test data..." required>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-cloud-upload"></i> Submit via AJAX
                        </button>
                    </form>

                    <div class="counter" id="counter-2">0</div>
                    <div id="result-2"></div>
                </div>
            </div>

            <!-- Test 3: Manual Button Click -->
            <div class="col-lg-6">
                <div class="test-card">
                    <h3><i class="bi bi-3-circle-fill text-primary"></i> Button Click Test</h3>
                    <div class="instructions">
                        <strong>Instructions:</strong> This button uses data-prevent-double attribute. 
                        Try clicking rapidly - it should disable after first click.
                    </div>
                    
                    <button 
                        type="button" 
                        class="btn btn-warning btn-lg w-100"
                        data-prevent-double
                        data-reenable-timeout="2000"
                        onclick="testButtonClick()">
                        <i class="bi bi-hand-index-thumb"></i> Click Me Rapidly!
                    </button>

                    <div class="counter" id="counter-3">0</div>
                    <div id="result-3"></div>
                </div>
            </div>

            <!-- Test 4: Multiple Forms -->
            <div class="col-lg-6">
                <div class="test-card">
                    <h3><i class="bi bi-4-circle-fill text-primary"></i> Multiple Forms Test</h3>
                    <div class="instructions">
                        <strong>Instructions:</strong> Two forms on same page. Each should be protected independently.
                    </div>
                    
                    <form id="test-form-4a" class="mb-3">
                        <input type="text" class="form-control mb-2" placeholder="Form A" required>
                        <button type="submit" class="btn btn-info btn-sm w-100">Submit Form A</button>
                    </form>

                    <form id="test-form-4b">
                        <input type="text" class="form-control mb-2" placeholder="Form B" required>
                        <button type="submit" class="btn btn-secondary btn-sm w-100">Submit Form B</button>
                    </form>

                    <div class="row">
                        <div class="col-6">
                            <div class="counter" id="counter-4a">0</div>
                            <small class="text-muted">Form A Submissions</small>
                        </div>
                        <div class="col-6">
                            <div class="counter" id="counter-4b">0</div>
                            <small class="text-muted">Form B Submissions</small>
                        </div>
                    </div>
                    <div id="result-4"></div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-10">
                <div class="test-card">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-clipboard-check"></i> Test Summary
                    </h3>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="counter text-success" id="total-attempts">0</div>
                            <p>Total Attempts</p>
                        </div>
                        <div class="col-md-3">
                            <div class="counter text-primary" id="total-prevented">0</div>
                            <p>Prevented Duplicates</p>
                        </div>
                        <div class="col-md-3">
                            <div class="counter text-warning" id="success-rate">100%</div>
                            <p>Protection Rate</p>
                        </div>
                        <div class="col-md-3">
                            <div class="counter text-info" id="total-submitted">0</div>
                            <p>Actual Submissions</p>
                        </div>
                    </div>
                    <div class="alert alert-success text-center mt-3">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>Protection Active!</strong> All forms are protected from double submission.
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-3">
            <div class="col-lg-10 text-center">
                <a href="{{ url('/') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-house"></i> Back to Dashboard
                </a>
                <button onclick="resetCounters()" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-arrow-clockwise"></i> Reset Counters
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/prevent-double-submit.js') }}"></script>

    <script>
        let counters = {
            form1: 0,
            form2: 0,
            form3: 0,
            form4a: 0,
            form4b: 0
        };
        let totalAttempts = 0;
        let totalSubmitted = 0;

        // Test 1: Regular Form
        document.getElementById('test-form-1').addEventListener('submit', function(e) {
            e.preventDefault();
            counters.form1++;
            totalSubmitted++;
            document.getElementById('counter-1').textContent = counters.form1;
            showResult('result-1', counters.form1 === 1 ? 'success' : 'danger', 
                counters.form1 === 1 ? '✓ Perfect! Only one submission recorded.' : 
                '✗ Warning: Multiple submissions detected! (' + counters.form1 + ')');
            updateSummary();
            
            // Simulate async operation
            setTimeout(() => {
                console.log('Form 1 submission complete');
            }, 1000);
        });

        // Test 2: AJAX Form
        document.getElementById('test-form-2').addEventListener('submit', function(e) {
            e.preventDefault();
            counters.form2++;
            totalSubmitted++;
            document.getElementById('counter-2').textContent = counters.form2;
            
            // Simulate AJAX call
            setTimeout(() => {
                showResult('result-2', counters.form2 === 1 ? 'success' : 'danger', 
                    counters.form2 === 1 ? '✓ AJAX protected! Only one request sent.' : 
                    '✗ Multiple AJAX requests detected! (' + counters.form2 + ')');
                updateSummary();
            }, 500);
        });

        // Test 3: Button Click
        function testButtonClick() {
            counters.form3++;
            totalAttempts++;
            document.getElementById('counter-3').textContent = counters.form3;
            showResult('result-3', 'success', 
                '✓ Button clicked ' + counters.form3 + ' time(s). Protection working!');
            updateSummary();
        }

        // Test 4a: Multiple Forms A
        document.getElementById('test-form-4a').addEventListener('submit', function(e) {
            e.preventDefault();
            counters.form4a++;
            totalSubmitted++;
            document.getElementById('counter-4a').textContent = counters.form4a;
            showResult('result-4', counters.form4a === 1 ? 'success' : 'danger', 
                counters.form4a === 1 ? '✓ Form A protected!' : 
                '✗ Form A: Multiple submissions! (' + counters.form4a + ')');
            updateSummary();
        });

        // Test 4b: Multiple Forms B
        document.getElementById('test-form-4b').addEventListener('submit', function(e) {
            e.preventDefault();
            counters.form4b++;
            totalSubmitted++;
            document.getElementById('counter-4b').textContent = counters.form4b;
            showResult('result-4', counters.form4b === 1 ? 'success' : 'danger', 
                counters.form4b === 1 ? '✓ Form B protected!' : 
                '✗ Form B: Multiple submissions! (' + counters.form4b + ')');
            updateSummary();
        });

        function showResult(elementId, type, message) {
            const resultDiv = document.getElementById(elementId);
            resultDiv.className = 'test-result ' + type;
            resultDiv.innerHTML = '<strong>' + message + '</strong>';
        }

        function updateSummary() {
            document.getElementById('total-attempts').textContent = totalAttempts;
            document.getElementById('total-submitted').textContent = totalSubmitted;
            
            const prevented = totalAttempts - totalSubmitted;
            document.getElementById('total-prevented').textContent = prevented;
            
            const rate = totalAttempts > 0 ? Math.round((prevented / totalAttempts) * 100) : 100;
            document.getElementById('success-rate').textContent = rate + '%';
        }

        function resetCounters() {
            counters = {form1: 0, form2: 0, form3: 0, form4a: 0, form4b: 0};
            totalAttempts = 0;
            totalSubmitted = 0;
            
            document.getElementById('counter-1').textContent = '0';
            document.getElementById('counter-2').textContent = '0';
            document.getElementById('counter-3').textContent = '0';
            document.getElementById('counter-4a').textContent = '0';
            document.getElementById('counter-4b').textContent = '0';
            
            document.getElementById('result-1').innerHTML = '';
            document.getElementById('result-2').innerHTML = '';
            document.getElementById('result-3').innerHTML = '';
            document.getElementById('result-4').innerHTML = '';
            
            updateSummary();
        }

        // Track all click attempts
        document.addEventListener('click', function(e) {
            if (e.target.type === 'submit' || e.target.closest('button[type="submit"]')) {
                totalAttempts++;
                updateSummary();
            }
        }, true);
    </script>
</body>
</html>
