from selenium import webdriver
from selenium.webdriver.common.by import By
import time

driver = webdriver.Chrome()
driver.get("http://127.0.0.1:8000/login")
time.sleep(2)

print("URL:", driver.current_url)
print("TITLE:", driver.title)

print("\n=== INPUT ===")
for i, e in enumerate(driver.find_elements(By.TAG_NAME, "input")):
    print(i, {
        "type": e.get_attribute("type"),
        "name": e.get_attribute("name"),
        "id": e.get_attribute("id"),
        "value": e.get_attribute("value"),
        "placeholder": e.get_attribute("placeholder"),
        "displayed": e.is_displayed(),
    })

print("\n=== BUTTON ===")
for i, e in enumerate(driver.find_elements(By.TAG_NAME, "button")):
    print(i, {
        "text": e.text,
        "type": e.get_attribute("type"),
        "name": e.get_attribute("name"),
        "value": e.get_attribute("value"),
        "id": e.get_attribute("id"),
        "displayed": e.is_displayed(),
    })

print("\n=== SELECT ===")
for i, e in enumerate(driver.find_elements(By.TAG_NAME, "select")):
    print(i, {
        "name": e.get_attribute("name"),
        "id": e.get_attribute("id"),
        "displayed": e.is_displayed(),
    })

print("\n=== LINKS ===")
for i, e in enumerate(driver.find_elements(By.TAG_NAME, "a")):
    text = e.text.strip()
    if text:
        print(i, repr(text), e.get_attribute("href"))

driver.save_screenshot("qa/selenium/login-dom.png")
driver.quit()
