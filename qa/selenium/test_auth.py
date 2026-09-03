import os
from pathlib import Path

import pytest
from selenium import webdriver
from selenium.common.exceptions import NoSuchElementException
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


BASE_URL = os.getenv("QA_BASE_URL", "http://127.0.0.1:8000")

STUDENT_USER = os.getenv("QA_STUDENT_USER", "")
STUDENT_PASSWORD = os.getenv("QA_STUDENT_PASSWORD", "")

ADMIN_USER = os.getenv("QA_ADMIN_USER", "")
ADMIN_PASSWORD = os.getenv("QA_ADMIN_PASSWORD", "")

TEACHER_USER = os.getenv("QA_TEACHER_USER", "")
TEACHER_PASSWORD = os.getenv("QA_TEACHER_PASSWORD", "")

SCREENSHOT_DIR = Path(__file__).parent / "screenshots"
SCREENSHOT_DIR.mkdir(exist_ok=True)


@pytest.fixture
def driver(request):
    options = webdriver.ChromeOptions()

    options.add_argument("--start-maximized")
    options.add_argument("--disable-notifications")

    browser = webdriver.Chrome(options=options)
    browser.set_page_load_timeout(20)

    yield browser

    # Screenshot otomatis apabila test gagal.
    report = getattr(request.node, "rep_call", None)

    if report and report.failed:
        browser.save_screenshot(
            str(SCREENSHOT_DIR / f"{request.node.name}.png")
        )

    browser.quit()


@pytest.hookimpl(hookwrapper=True, tryfirst=True)
def pytest_runtest_makereport(item, call):
    outcome = yield
    report = outcome.get_result()

    if report.when == "call":
        item.rep_call = report


def wait(driver):
    return WebDriverWait(driver, 10)


def find_first(driver, selectors):
    for by, selector in selectors:
        elements = driver.find_elements(by, selector)

        for element in elements:
            if element.is_displayed() and element.is_enabled():
                return element

    raise AssertionError(
        f"Elemen visible tidak ditemukan. Selector dicoba: {selectors}"
    )


def open_login(driver):
    driver.get(f"{BASE_URL}/login")

    wait(driver).until(
        lambda d: "/login" in d.current_url
    )


def choose_portal(driver, portal):
    elements = driver.find_elements(By.NAME, "login_as")

    for element in elements:
        if (
            element.is_displayed()
            and element.is_enabled()
            and element.get_attribute("value") == portal
        ):
            driver.execute_script(
                "arguments[0].click();",
                element,
            )

            wait(driver).until(
                lambda d: element.is_selected()
            )

            return

    raise AssertionError(
        f"Portal login visible '{portal}' tidak ditemukan."
    )


def fill_credentials(driver, username, password):
    username_input = find_first(
        driver,
        [
            (By.NAME, "nis"),
            (By.NAME, "username"),
            (By.NAME, "email"),
            (By.ID, "nis"),
            (By.ID, "username"),
            (By.ID, "email"),
        ],
    )

    password_input = find_first(
        driver,
        [
            (By.NAME, "password"),
            (By.ID, "password"),
        ],
    )

    username_input.clear()
    username_input.send_keys(username)

    password_input.clear()
    password_input.send_keys(password)


def submit_login(driver):
    button = find_first(
        driver,
        [
            (By.ID, "login-submit-btn"),
            (By.CSS_SELECTOR, "button[type='submit']"),
            (By.CSS_SELECTOR, "input[type='submit']"),
        ],
    )

    form = button.find_element(
        By.XPATH,
        "./ancestor::form"
    )

    driver.execute_script(
        "arguments[0].click();",
        button,
    )

    try:
        WebDriverWait(driver, 10).until(
            EC.staleness_of(form)
        )
    except Exception:
        WebDriverWait(driver, 10).until(
            lambda d: d.execute_script(
                "return document.readyState"
            ) == "complete"
        )


def login(driver, username, password, portal):
    open_login(driver)

    choose_portal(driver, portal)
    fill_credentials(driver, username, password)
    submit_login(driver)


def logout(driver):
    forms = driver.find_elements(
        By.CSS_SELECTOR,
        "form[action*='logout']",
    )

    if forms:
        driver.execute_script(
            "arguments[0].submit();",
            forms[0],
        )

        wait(driver).until(
            lambda d: "/login" in d.current_url
        )

        return

    # Fallback tombol logout.
    buttons = driver.find_elements(
        By.XPATH,
        "//*[contains("
        "translate(normalize-space(text()),"
        "'ABCDEFGHIJKLMNOPQRSTUVWXYZ',"
        "'abcdefghijklmnopqrstuvwxyz'),"
        "'logout'"
        ") or contains("
        "translate(normalize-space(text()),"
        "'ABCDEFGHIJKLMNOPQRSTUVWXYZ',"
        "'abcdefghijklmnopqrstuvwxyz'),"
        "'keluar'"
        ")]",
    )

    assert buttons, "Tombol/form logout tidak ditemukan."

    driver.execute_script(
        "arguments[0].click();",
        buttons[0],
    )


def page_contains_any(driver, texts):
    content = driver.page_source.lower()

    return any(
        text.lower() in content
        for text in texts
    )


def assert_access_denied(driver):
    denied_texts = [
        "403",
        "forbidden",
        "unauthorized",
        "tidak memiliki akses",
        "akses ditolak",
        "tidak diizinkan",
    ]

    protected_denied = page_contains_any(
        driver,
        denied_texts,
    )

    redirected_login = "/login" in driver.current_url

    assert protected_denied or redirected_login


# =========================================================
# AUTH-001
# =========================================================

def test_auth_001_student_valid_login(driver):
    assert STUDENT_USER, "QA_STUDENT_USER belum di-set."
    assert STUDENT_PASSWORD, "QA_STUDENT_PASSWORD belum di-set."

    login(
        driver,
        STUDENT_USER,
        STUDENT_PASSWORD,
        "siswa",
    )

    assert "/login" not in driver.current_url

    assert not page_contains_any(
        driver,
        [
            "kode otp",
            "verifikasi otp",
            "kirim otp",
        ],
    )


# =========================================================
# AUTH-002
# =========================================================

def test_auth_002_wrong_password(driver):
    assert STUDENT_USER

    open_login(driver)

    choose_portal(driver, "siswa")

    fill_credentials(
        driver,
        STUDENT_USER,
        "PASSWORD_SALAH_QA",
    )

    submit_login(driver)

    wait(driver).until(
        lambda d: "/login" in d.current_url
    )

    assert "/login" in driver.current_url


# =========================================================
# AUTH-003
# =========================================================

def test_auth_003_unknown_student(driver):
    open_login(driver)

    choose_portal(driver, "siswa")

    fill_credentials(
        driver,
        "999999999999",
        "password-tidak-valid",
    )

    submit_login(driver)

    wait(driver).until(
        lambda d: "/login" in d.current_url
    )

    assert "/login" in driver.current_url


# =========================================================
# AUTH-004
# =========================================================

def test_auth_004_student_cannot_use_staff_portal(driver):
    assert STUDENT_USER
    assert STUDENT_PASSWORD

    open_login(driver)

    choose_portal(driver, "staff")

    fill_credentials(
        driver,
        STUDENT_USER,
        STUDENT_PASSWORD,
    )

    submit_login(driver)

    wait(driver).until(
        lambda d: True
    )

    assert "/admin" not in driver.current_url


# =========================================================
# AUTH-005
# =========================================================

def test_auth_005_admin_valid_login(driver):
    if not ADMIN_USER or not ADMIN_PASSWORD:
        pytest.skip("Credential admin QA belum di-set.")

    login(
        driver,
        ADMIN_USER,
        ADMIN_PASSWORD,
        "staff",
    )

    assert "/admin" in driver.current_url


# =========================================================
# AUTH-006
# =========================================================

def test_auth_006_teacher_valid_login(driver):
    if not TEACHER_USER or not TEACHER_PASSWORD:
        pytest.skip("Credential guru QA belum di-set.")

    login(
        driver,
        TEACHER_USER,
        TEACHER_PASSWORD,
        "staff",
    )

    assert "/login" not in driver.current_url
    assert "/admin" not in driver.current_url


# =========================================================
# AUTH-007
# =========================================================

def test_auth_007_logout(driver):
    assert STUDENT_USER
    assert STUDENT_PASSWORD

    login(
        driver,
        STUDENT_USER,
        STUDENT_PASSWORD,
        "siswa",
    )

    logout(driver)

    assert "/login" in driver.current_url


# =========================================================
# AUTH-008
# =========================================================

def test_auth_008_back_after_logout_is_protected(driver):
    assert STUDENT_USER
    assert STUDENT_PASSWORD

    login(
        driver,
        STUDENT_USER,
        STUDENT_PASSWORD,
        "siswa",
    )

    logout(driver)

    driver.back()
    driver.refresh()

    wait(driver).until(
        lambda d: True
    )

    assert (
        "/login" in driver.current_url
        or page_contains_any(
            driver,
            [
                "unauthorized",
                "forbidden",
                "403",
            ],
        )
    )


# =========================================================
# AUTH-009
# =========================================================

def test_auth_009_student_cannot_access_admin(driver):
    assert STUDENT_USER
    assert STUDENT_PASSWORD

    login(
        driver,
        STUDENT_USER,
        STUDENT_PASSWORD,
        "siswa",
    )

    driver.get(f"{BASE_URL}/admin")

    assert_access_denied(driver)


# =========================================================
# AUTH-010
# =========================================================

def test_auth_010_admin_cannot_use_student_dashboard(driver):
    if not ADMIN_USER or not ADMIN_PASSWORD:
        pytest.skip("Credential admin QA belum di-set.")

    login(
        driver,
        ADMIN_USER,
        ADMIN_PASSWORD,
        "staff",
    )

    driver.get(f"{BASE_URL}/dashboard")

    assert_access_denied(driver)


# =========================================================
# AUTH-011
# =========================================================

def test_auth_011_login_contains_no_otp_or_phone(driver):
    open_login(driver)

    forbidden_terms = [
        "kode otp",
        "verifikasi otp",
        "resend otp",
        "kirim ulang otp",
        "nomor hp",
        "nomor telepon",
        "whatsapp",
        "fonnte",
    ]

    source = driver.page_source.lower()

    found = [
        term
        for term in forbidden_terms
        if term in source
    ]

    assert found == [], (
        f"Referensi OTP/nomor telepon masih ditemukan: {found}"
    )