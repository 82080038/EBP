# MODUL 40 — LOAD TESTING SCENARIOS

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Scenarios load testing untuk aplikasi Tour Guide menggunakan JMeter dan k6.

---

## 2. JMETER SETUP

### 2.1 Install JMeter

```bash
# Download JMeter
wget https://downloads.apache.org//jmeter/binaries/apache-jmeter-5.5.tgz
tar -xzf apache-jmeter-5.5.tgz
cd apache-jmeter-5.5/bin
./jmeter
```

### 2.2 JMeter Test Plan Structure

```
Test Plan
├── Thread Group
│   ├── HTTP Request Defaults
│   ├── HTTP Cookie Manager
│   ├── HTTP Header Manager
│   ├── Login Request
│   ├── Dashboard Request
│   ├── Search Guide Request
│   ├── Create Booking Request
│   └── View Ticket Request
├── Listener
│   ├── View Results Tree
│   ├── Summary Report
│   └── Aggregate Report
└── Assertions
    ├── Response Assertion
    └── Duration Assertion
```

---

## 3. JMETER TEST SCENARIOS

### 3.1 Scenario 1: Normal Load

**Purpose:** Test system under normal user load

**Configuration:**
- Users: 100
- Ramp-up: 10 seconds
- Duration: 5 minutes
- Loop: 1

**Test Steps:**
1. Login (POST /api/auth/login)
2. View Dashboard (GET /api/dashboard)
3. Search Guides (GET /api/guides)
4. View Guide Profile (GET /api/guides/{id})
5. Logout (POST /api/auth/logout)

**Expected Results:**
- Response time < 500ms
- Error rate < 1%
- Throughput > 50 requests/second

### 3.2 Scenario 2: Peak Load

**Purpose:** Test system during peak hours

**Configuration:**
- Users: 500
- Ramp-up: 30 seconds
- Duration: 10 minutes
- Loop: 1

**Test Steps:**
1. Login (POST /api/auth/login)
2. View Dashboard (GET /api/dashboard)
3. Search Guides (GET /api/guides)
4. View Guide Profile (GET /api/guides/{id})
5. Create Booking (POST /api/bookings)
6. View My Bookings (GET /api/bookings/my)
7. Logout (POST /api/auth/logout)

**Expected Results:**
- Response time < 1 second
- Error rate < 2%
- Throughput > 100 requests/second

### 3.3 Scenario 3: Stress Test

**Purpose:** Find breaking point of system

**Configuration:**
- Users: 1000
- Ramp-up: 60 seconds
- Duration: 15 minutes
- Loop: 1

**Test Steps:**
1. Login (POST /api/auth/login)
2. View Dashboard (GET /api/dashboard)
3. Search Guides (GET /api/guides)
4. Create Booking (POST /api/bookings)
5. Buy Ticket (POST /api/tickets/buy)
6. View Notifications (GET /api/notifications)
7. Logout (POST /api/auth/logout)

**Expected Results:**
- System should handle up to 800 concurrent users
- Response time < 2 seconds
- Error rate < 5%

### 3.4 Scenario 4: Endurance Test

**Purpose:** Test system stability over time

**Configuration:**
- Users: 200
- Ramp-up: 20 seconds
- Duration: 2 hours
- Loop: 10

**Test Steps:**
1. Login (POST /api/auth/login)
2. View Dashboard (GET /api/dashboard)
3. Search Guides (GET /api/guides)
4. View Guide Profile (GET /api/guides/{id})
5. Logout (POST /api/auth/logout)

**Expected Results:**
- No memory leaks
- Response time stable over time
- Error rate < 1%

### 3.5 Scenario 5: Spike Test

**Purpose:** Test system response to sudden traffic spike

**Configuration:**
- Users: 50 (baseline)
- Spike: 500 users
- Spike duration: 1 minute
- Total duration: 10 minutes

**Test Steps:**
1. Baseline: 50 users for 2 minutes
2. Spike: 500 users for 1 minute
3. Recovery: 50 users for 7 minutes

**Expected Results:**
- System recovers within 2 minutes
- No data corruption
- Response time returns to baseline

---

## 4. K6 SETUP

### 4.1 Install k6

```bash
# Install k6
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

### 4.2 k6 Test Script

```javascript
// load-tests/normal-load.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
    stages: [
        { duration: '10s', target: 100 },  // Ramp up to 100 users
        { duration: '5m', target: 100 },   // Stay at 100 users
        { duration: '10s', target: 0 },    // Ramp down to 0
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'],  // 95% of requests < 500ms
        http_req_failed: ['rate<0.01'],    // Error rate < 1%
    },
};

const BASE_URL = 'https://tourguide.com/api';

export default function () {
    // Login
    let loginRes = http.post(`${BASE_URL}/auth/login`, JSON.stringify({
        email: 'test@example.com',
        password: 'password123'
    }), {
        headers: { 'Content-Type': 'application/json' },
    });
    
    check(loginRes, {
        'login successful': (r) => r.status === 200,
    });
    
    let token = loginRes.json('data.token');
    
    // View Dashboard
    let dashboardRes = http.get(`${BASE_URL}/dashboard`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    
    check(dashboardRes, {
        'dashboard loaded': (r) => r.status === 200,
    });
    
    // Search Guides
    let guidesRes = http.get(`${BASE_URL}/guides?location=Yogyakarta`, {
        headers: { 'Authorization': `Bearer ${token}` },
    });
    
    check(guidesRes, {
        'guides loaded': (r) => r.status === 200,
    });
    
    sleep(1);
}
```

---

## 5. K6 TEST SCENARIOS

### 5.1 Scenario 1: Normal Load

```javascript
// load-tests/normal-load.js
export let options = {
    stages: [
        { duration: '10s', target: 100 },
        { duration: '5m', target: 100 },
        { duration: '10s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'],
        http_req_failed: ['rate<0.01'],
    },
};
```

### 5.2 Scenario 2: Peak Load

```javascript
// load-tests/peak-load.js
export let options = {
    stages: [
        { duration: '30s', target: 500 },
        { duration: '10m', target: 500 },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<1000'],
        http_req_failed: ['rate<0.02'],
    },
};
```

### 5.3 Scenario 3: Stress Test

```javascript
// load-tests/stress-test.js
export let options = {
    stages: [
        { duration: '1m', target: 100 },
        { duration: '1m', target: 300 },
        { duration: '1m', target: 500 },
        { duration: '1m', target: 700 },
        { duration: '1m', target: 1000 },
        { duration: '10m', target: 1000 },
        { duration: '1m', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'],
        http_req_failed: ['rate<0.05'],
    },
};
```

### 5.4 Scenario 4: Endurance Test

```javascript
// load-tests/endurance-test.js
export let options = {
    stages: [
        { duration: '20s', target: 200 },
        { duration: '2h', target: 200 },
        { duration: '20s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'],
        http_req_failed: ['rate<0.01'],
    },
};
```

### 5.5 Scenario 5: Spike Test

```javascript
// load-tests/spike-test.js
export let options = {
    stages: [
        { duration: '2m', target: 50 },   // Baseline
        { duration: '1m', target: 500 },  // Spike
        { duration: '7m', target: 50 },   // Recovery
    ],
    thresholds: {
        http_req_duration: ['p(95)<1000'],
        http_req_failed: ['rate<0.05'],
    },
};
```

---

## 6. RUNNING TESTS

### 6.1 Run JMeter Test

```bash
cd apache-jmeter-5.5/bin
./jmeter -n -t test-plan.jmx -l results.jtl -e -o report
```

### 6.2 Run k6 Test

```bash
k6 run load-tests/normal-load.js
k6 run load-tests/peak-load.js
k6 run load-tests/stress-test.js
k6 run load-tests/endurance-test.js
k6 run load-tests/spike-test.js
```

### 6.3 Run with Output

```bash
# k6 with JSON output
k6 run --out json=results.json load-tests/normal-load.js

# k6 with HTML report
k6 run --out html=report.html load-tests/normal-load.js
```

---

## 7. METRICS TO MONITOR

### 7.1 Response Time

- Average response time
- 95th percentile response time
- 99th percentile response time
- Min/Max response time

### 7.2 Throughput

- Requests per second
- Transactions per second
- Bytes per second

### 7.3 Error Rate

- HTTP errors (4xx, 5xx)
- Timeout errors
- Connection errors
- Validation errors

### 7.4 Resource Utilization

- CPU usage
- Memory usage
- Disk I/O
- Network I/O
- Database connections

---

## 8. PERFORMANCE TARGETS

| Metric | Target | Acceptable | Critical |
|--------|--------|-----------|---------|
| Page Load Time | < 2s | < 3s | > 5s |
| API Response Time | < 500ms | < 1s | > 2s |
| Database Query Time | < 100ms | < 200ms | > 500ms |
| Error Rate | < 1% | < 2% | > 5% |
| Throughput | > 100 req/s | > 50 req/s | < 20 req/s |
| Concurrent Users | 1000+ | 500+ | < 200 |

---

## 9. ANALYZING RESULTS

### 9.1 JMeter Report

**View Results Tree:**
- Check individual request responses
- Verify response codes
- Check response times

**Summary Report:**
- Average response time
- Min/Max response time
- Error percentage
- Throughput

**Aggregate Report:**
- 90th percentile
- 95th percentile
- 99th percentile
- Standard deviation

### 9.2 k6 Report

**Console Output:**
```
✓ login successful
✓ dashboard loaded
✓ guides loaded

checks.........................: 100.00% ✓ 30000 / 30000
data_received..................: 15 MB 50 kB/s
data_sent......................: 5.0 MB 17 kB/s
http_req_blocked...............: avg=10ms min=1ms med=8ms max=100ms p(95)=20ms
http_req_connecting............: avg=5ms min=1ms med=4ms max=50ms p(95)=10ms
http_req_duration..............: avg=250ms min=50ms med=200ms max=800ms p(95)=450ms
http_req_failed................: 0.00% ✓ 0 / 30000
http_req_receiving.............: avg=10ms min=1ms med=8ms max=50ms p(95)=20ms
http_req_sending...............: avg=5ms min=1ms med=4ms max=20ms p(95)=10ms
http_req_tls_handshaking.......: avg=0s min=0s med=0s max=1s p(95)=0s
http_req_waiting...............: avg=235ms min=40ms med=190ms max=750ms p(95)=420ms
http_reqs......................: 30000 100/s
iteration_duration.............: avg=1.2s min=1s med=1.1s max=2s p(95)=1.5s
iterations.....................: 30000 100/s
vus............................: 100 min=100 max=100
vus_max........................: 100 min=100 max=100
```

---

## 10. TROUBLESHOOTING

### 10.1 High Response Time

**Possible Causes:**
- Database queries slow
- No database indexes
- Server overloaded
- Network latency
- Code inefficiency

**Solutions:**
- Add database indexes
- Optimize queries
- Implement caching
- Scale horizontally
- Optimize code

### 10.2 High Error Rate

**Possible Causes:**
- Database connection pool exhausted
- Server timeout
- Memory issues
- Network issues
- Application bugs

**Solutions:**
- Increase connection pool
- Increase timeout
- Add more memory
- Check network
- Fix bugs

### 10.3 Memory Leaks

**Possible Causes:**
- Unclosed connections
- Large objects in memory
- Circular references
- Caching issues

**Solutions:**
- Close connections properly
- Optimize memory usage
- Fix circular references
- Implement cache expiration

---

## 11. CI/CD INTEGRATION

### 11.1 GitHub Actions for k6

```yaml
# .github/workflows/load-test.yml
name: Load Testing

on:
  schedule:
    - cron: '0 2 * * *'  # Run daily at 2 AM
  workflow_dispatch:

jobs:
  load-test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Install k6
      run: |
        sudo gpg -k
        sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
        echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
        sudo apt-get update
        sudo apt-get install k6
    
    - name: Run load test
      run: k6 run load-tests/normal-load.js
    
    - name: Upload results
      uses: actions/upload-artifact@v2
      with:
        name: load-test-results
        path: results.json
```

---

## 12. BEST PRACTICES

### 12.1 Test Design

- Use realistic user scenarios
- Include think time between requests
- Test different user paths
- Use realistic data
- Test during off-peak hours

### 12.2 Test Execution

- Run tests in staging environment
- Monitor system resources during test
- Have rollback plan ready
- Document test results
- Compare with baseline

### 12.3 Result Analysis

- Focus on 95th percentile
- Check error rates
- Monitor resource usage
- Identify bottlenecks
- Create action items

---

## 13. RESOURCES

### 13.1 Tools

- **JMeter**: https://jmeter.apache.org/
- **k6**: https://k6.io/
- **Gatling**: https://gatling.io/
- **Locust**: https://locust.io/

### 13.2 Documentation

- JMeter User Manual: https://jmeter.apache.org/usermanual/index.html
- k6 Documentation: https://k6.io/docs/
- Load Testing Best Practices: https://www.gatling.io/gatling-tutorials/

---

> **Modul Selanjutnya:** `41_VISUAL_DIAGRAMS.md`
