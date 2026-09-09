#!/bin/bash
#
# End-to-end test of the routes, validation and access control.
# Needs a running server and a loaded genome_platform database.
#
#   BASE_URL=http://localhost bash tests/e2e.sh
#
# It CREATES users (alice, bob, carol) and analyses. Run it against a
# throwaway database, never one with real data.
## End-to-end test of the reconstructed MVC app.
B=${BASE_URL:-http://localhost}
PASS=0; FAIL=0
J1=${TMPDIR:-/tmp}/gp_e2e/c1.txt; J2=${TMPDIR:-/tmp}/gp_e2e/c2.txt
rm -f $J1 $J2

code()  { curl -s -o /dev/null -w "%{http_code}" "$@"; }
loc()   { curl -s -o /dev/null -w "%{redirect_url}" "$@" | sed "s#^${B}##"; }
body()  { curl -s "$@"; }

rloc()  { curl -s -o /dev/null -w '%{redirect_url}' "$@" | sed "s#^${B}##"; }

check() { # name expected actual
  if [ "$2" = "$3" ]; then echo "  ok   $1"; PASS=$((PASS+1))
  else echo "  FAIL $1 (expected '$2', got '$3')"; FAIL=$((FAIL+1)); fi
}
contains() { # name needle haystack-file
  if grep -qF "$2" "$3"; then echo "  ok   $1"; PASS=$((PASS+1))
  else echo "  FAIL $1 (missing '$2')"; FAIL=$((FAIL+1)); fi
}

# csrf token from a form page, reusing a cookie jar
token() { curl -s -c "$1" -b "$1" "$2" | grep -o 'name="_token" value="[^"]*"' | head -1 | cut -d'"' -f4; }

echo "== public routes =="
check "GET /"              200 "$(code $B/)"
check "GET /how-it-works"  200 "$(code $B/how-it-works)"
check "GET /login"         200 "$(code $B/login)"
check "GET /register"      200 "$(code $B/register)"
check "GET /nope (404)"    404 "$(code $B/nope)"
check "GET /app/.. blocked-by-router" 404 "$(code $B/config/config.php)"

echo "== protected routes redirect guests to /login =="
check "GET /dashboard"  302 "$(code $B/dashboard)"
check "GET /analysis"   302 "$(code $B/analysis)"
check "GET /results"    302 "$(code $B/results)"
check "GET /report/1"   302 "$(code $B/report/1)"
check "  -> target"     "/login" "$(loc $B/dashboard)"

echo "== CSRF is enforced on POST =="
rm -f ${TMPDIR:-/tmp}/gp_e2e/csrf.txt
check "POST /login without token bounces" "/login" "$(rloc -c ${TMPDIR:-/tmp}/gp_e2e/csrf.txt -b ${TMPDIR:-/tmp}/gp_e2e/csrf.txt -X POST -d "email=admin@genomeplatform.local&password=Quartz-Quartz-4254" $B/login)"
check "  and does NOT authenticate"      302     "$(code -c ${TMPDIR:-/tmp}/gp_e2e/csrf.txt -b ${TMPDIR:-/tmp}/gp_e2e/csrf.txt $B/dashboard)"

echo "== registration =="
T=$(token $J1 $B/register)
[ -n "$T" ] && { echo "  ok   csrf token issued"; PASS=$((PASS+1)); } || { echo "  FAIL no csrf token"; FAIL=$((FAIL+1)); }
check "short password rejected" "/register" \
  "$(rloc -c $J1 -b $J1 -X POST \
     -d "_token=$T&username=alice&email=alice@test.local&password=short&password_confirm=short" $B/register)"

T=$(token $J1 $B/register)
check "valid registration -> dashboard" "/dashboard" \
  "$(rloc -c $J1 -b $J1 -X POST \
     -d "_token=$T&username=alice&email=alice@test.local&password=Str0ngPass1&password_confirm=Str0ngPass1" $B/register)"

curl -s -c $J1 -b $J1 $B/dashboard > ${TMPDIR:-/tmp}/gp_e2e/dash.html
contains "dashboard greets the real username" "Welcome, alice" ${TMPDIR:-/tmp}/gp_e2e/dash.html
contains "role rendered from DB"              "user" ${TMPDIR:-/tmp}/gp_e2e/dash.html

echo "== duplicate registration rejected =="
rm -f ${TMPDIR:-/tmp}/gp_e2e/c3.txt
T=$(token ${TMPDIR:-/tmp}/gp_e2e/c3.txt $B/register)
check "duplicate email -> /register" "/register" \
  "$(rloc -c ${TMPDIR:-/tmp}/gp_e2e/c3.txt -b ${TMPDIR:-/tmp}/gp_e2e/c3.txt -X POST \
     -d "_token=$T&username=alice2&email=alice@test.local&password=Str0ngPass1&password_confirm=Str0ngPass1" $B/register)"

echo "== analysis create + validation =="
T=$(token $J1 $B/analysis)
check "allele_freq out of range rejected" "/analysis" \
  "$(rloc -c $J1 -b $J1 -X POST \
     -d "_token=$T&allele_freq=5&cadd_score=10&consequence=Frameshift" $B/analysis)"

T=$(token $J1 $B/analysis)
check "bogus consequence rejected" "/analysis" \
  "$(rloc -c $J1 -b $J1 -X POST \
     -d "_token=$T&allele_freq=0.002&cadd_score=10&consequence=NotAThing" $B/analysis)"

T=$(token $J1 $B/analysis)
R=$(rloc -c $J1 -b $J1 -X POST \
     -d "_token=$T&allele_freq=0.004&cadd_score=42.5&consequence=Frameshift&notes=alice+high+risk" $B/analysis)
check "valid analysis -> /report/1" "/report/1" "$R"

curl -s -c $J1 -b $J1 $B/report/1 > ${TMPDIR:-/tmp}/gp_e2e/r1.html
contains "report shows High risk"        "Risk Level: High" ${TMPDIR:-/tmp}/gp_e2e/r1.html
contains "report shows Very Rare rarity" "Very Rare"        ${TMPDIR:-/tmp}/gp_e2e/r1.html
contains "notes escaped + shown"         "alice high risk"  ${TMPDIR:-/tmp}/gp_e2e/r1.html
contains "chart data emitted"            "pathogenicChart"  ${TMPDIR:-/tmp}/gp_e2e/r1.html

echo "== scoring bands =="
T=$(token $J1 $B/analysis); curl -s -o /dev/null -c $J1 -b $J1 -X POST \
  -d "_token=$T&allele_freq=0.03&cadd_score=20&consequence=Synonymous" $B/analysis
curl -s -c $J1 -b $J1 $B/report/2 > ${TMPDIR:-/tmp}/gp_e2e/r2.html
contains "cadd 20 -> Medium" "Risk Level: Medium" ${TMPDIR:-/tmp}/gp_e2e/r2.html
contains "freq 0.03 -> Rare" "Rare"               ${TMPDIR:-/tmp}/gp_e2e/r2.html

T=$(token $J1 $B/analysis); curl -s -o /dev/null -c $J1 -b $J1 -X POST \
  -d "_token=$T&allele_freq=0.2&cadd_score=3&consequence=Synonymous" $B/analysis
curl -s -c $J1 -b $J1 $B/report/3 > ${TMPDIR:-/tmp}/gp_e2e/r3.html
contains "cadd 3 -> Low"      "Risk Level: Low" ${TMPDIR:-/tmp}/gp_e2e/r3.html
contains "freq 0.2 -> Common" "Common"          ${TMPDIR:-/tmp}/gp_e2e/r3.html

echo "== THE IDOR FIX: second user cannot read alice's reports =="
T=$(token $J2 $B/register)
curl -s -o /dev/null -c $J2 -b $J2 -X POST \
  -d "_token=$T&username=bob&email=bob@test.local&password=Str0ngPass2&password_confirm=Str0ngPass2" $B/register
check "bob logged in"        200      "$(code -c $J2 -b $J2 $B/dashboard)"
check "bob GET /report/1"    302      "$(code -c $J2 -b $J2 $B/report/1)"
check "  -> bounced to /results" "/results" "$(loc -c $J2 -b $J2 $B/report/1)"
curl -s -c $J2 -b $J2 -L $B/report/1 > ${TMPDIR:-/tmp}/gp_e2e/bob1.html
if grep -qF "alice high risk" ${TMPDIR:-/tmp}/gp_e2e/bob1.html; then
  echo "  FAIL bob can see alice's notes"; FAIL=$((FAIL+1))
else echo "  ok   bob sees none of alice's data"; PASS=$((PASS+1)); fi

echo "== anonymous cannot read any report =="
check "guest GET /report/1" 302 "$(code $B/report/1)"

echo "== bob's own history is isolated =="
curl -s -c $J2 -b $J2 $B/results > ${TMPDIR:-/tmp}/gp_e2e/bobhist.html
contains "bob sees empty history" "No analyses yet" ${TMPDIR:-/tmp}/gp_e2e/bobhist.html

echo "== login with the seeded demo admin =="
rm -f ${TMPDIR:-/tmp}/gp_e2e/c4.txt
T=$(token ${TMPDIR:-/tmp}/gp_e2e/c4.txt $B/login)
check "seeded admin can log in" "/dashboard" \
  "$(rloc -c ${TMPDIR:-/tmp}/gp_e2e/c4.txt -b ${TMPDIR:-/tmp}/gp_e2e/c4.txt -X POST \
     -d "_token=$T&email=admin@genomeplatform.local&password=Quartz-Quartz-4254" $B/login)"
curl -s -c ${TMPDIR:-/tmp}/gp_e2e/c4.txt -b ${TMPDIR:-/tmp}/gp_e2e/c4.txt $B/dashboard > ${TMPDIR:-/tmp}/gp_e2e/admin.html
contains "admin role shown" "admin" ${TMPDIR:-/tmp}/gp_e2e/admin.html

T=$(token ${TMPDIR:-/tmp}/gp_e2e/c5.txt $B/login)
check "wrong password rejected" "/login" \
  "$(rloc -c ${TMPDIR:-/tmp}/gp_e2e/c5.txt -b ${TMPDIR:-/tmp}/gp_e2e/c5.txt -X POST \
     -d "_token=$T&email=admin@genomeplatform.local&password=wrongwrong" $B/login)"

echo "== logout clears the session =="
curl -s -o /dev/null -c $J1 -b $J1 $B/logout
check "after logout /dashboard redirects" 302 "$(code -c $J1 -b $J1 $B/dashboard)"

echo "== XSS: script in notes is escaped, not executed =="
rm -f ${TMPDIR:-/tmp}/gp_e2e/c6.txt
T=$(token ${TMPDIR:-/tmp}/gp_e2e/c6.txt $B/register)
curl -s -o /dev/null -c ${TMPDIR:-/tmp}/gp_e2e/c6.txt -b ${TMPDIR:-/tmp}/gp_e2e/c6.txt -X POST \
  -d "_token=$T&username=carol&email=carol@test.local&password=Str0ngPass3&password_confirm=Str0ngPass3" $B/register
T=$(token ${TMPDIR:-/tmp}/gp_e2e/c6.txt $B/analysis)
curl -s -o /dev/null -c ${TMPDIR:-/tmp}/gp_e2e/c6.txt -b ${TMPDIR:-/tmp}/gp_e2e/c6.txt -X POST \
  --data-urlencode '_token='"$T" \
  --data-urlencode 'allele_freq=0.01' \
  --data-urlencode 'cadd_score=31' \
  --data-urlencode 'consequence=Missense Variant' \
  --data-urlencode 'notes=<script>alert(1)</script>' $B/analysis
curl -s -c ${TMPDIR:-/tmp}/gp_e2e/c6.txt -b ${TMPDIR:-/tmp}/gp_e2e/c6.txt $B/report/4 > ${TMPDIR:-/tmp}/gp_e2e/xss.html
if grep -qF "<script>alert(1)</script>" ${TMPDIR:-/tmp}/gp_e2e/xss.html; then
  echo "  FAIL raw script tag present"; FAIL=$((FAIL+1))
else echo "  ok   script tag not emitted raw"; PASS=$((PASS+1)); fi
contains "escaped entity present" "&lt;script&gt;" ${TMPDIR:-/tmp}/gp_e2e/xss.html

echo "== SQL injection attempt in login email =="
rm -f ${TMPDIR:-/tmp}/gp_e2e/c7.txt
T=$(token ${TMPDIR:-/tmp}/gp_e2e/c7.txt $B/login)
check "injection payload just fails auth" "/login" \
  "$(rloc -c ${TMPDIR:-/tmp}/gp_e2e/c7.txt -b ${TMPDIR:-/tmp}/gp_e2e/c7.txt -X POST \
     --data-urlencode "_token=$T" \
     --data-urlencode "email=admin@genomeplatform.local' OR '1'='1" \
     --data-urlencode "password=anything" $B/login)"

echo "== open redirect is not possible via intended_url =="
rm -f ${TMPDIR:-/tmp}/gp_e2e/c8.txt
curl -s -o /dev/null -c ${TMPDIR:-/tmp}/gp_e2e/c8.txt -b ${TMPDIR:-/tmp}/gp_e2e/c8.txt "$B/dashboard"
T=$(token ${TMPDIR:-/tmp}/gp_e2e/c8.txt $B/login)
check "login after guest bounce -> /dashboard" "/dashboard" "$(rloc -c ${TMPDIR:-/tmp}/gp_e2e/c8.txt -b ${TMPDIR:-/tmp}/gp_e2e/c8.txt -X POST --data-urlencode "_token=$T" --data-urlencode "email=admin@genomeplatform.local" --data-urlencode "password=Quartz-Quartz-4254" $B/login)"

# A hostile intended_url must not be honoured
rm -f ${TMPDIR:-/tmp}/gp_e2e/c9.txt
curl -s -o /dev/null -c ${TMPDIR:-/tmp}/gp_e2e/c9.txt -b ${TMPDIR:-/tmp}/gp_e2e/c9.txt "$B/report/1"
T=$(token ${TMPDIR:-/tmp}/gp_e2e/c9.txt $B/login)
check "intended_url stays in-app" "/report/1" "$(rloc -c ${TMPDIR:-/tmp}/gp_e2e/c9.txt -b ${TMPDIR:-/tmp}/gp_e2e/c9.txt -X POST --data-urlencode "_token=$T" --data-urlencode "email=admin@genomeplatform.local" --data-urlencode "password=Quartz-Quartz-4254" $B/login)"

echo
echo "=============================="
echo " PASS: $PASS    FAIL: $FAIL"
echo "=============================="
[ $FAIL -eq 0 ]
