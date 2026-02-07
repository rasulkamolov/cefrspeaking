from playwright.sync_api import sync_playwright

def verify_layout():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        # Emulate iPhone 12 Pro (390 x 844)
        context = browser.new_context(viewport={'width': 390, 'height': 844})
        page = context.new_page()

        # Login
        page.goto("http://127.0.0.1:8080/pages/login.php")
        page.fill("input[name='email']", "student@jules.com")
        page.fill("input[name='password']", "student123")
        page.click("button[type='submit']")
        page.wait_for_url("**/student/dashboard.php")

        # Start Exam
        page.locator("a[href*='mode=full']").first.click()
        page.wait_for_url("**/exam/intro.php*")
        page.click("text=Start Now")
        page.wait_for_url("**/exam/runner.php*")

        # Verify Bottom Bar Visibility
        # We check if the 'Next' button or 'Timer' is visible within the viewport
        # Since it's fixed bottom, it should be at y ~ 744 (844 - 100)

        page.wait_for_selector("#timer-text")

        # Take screenshot of the bottom area specifically
        page.screenshot(path="runner_layout.png")
        print("Screenshot taken.")

        # Check if footer is visible
        footer_box = page.locator(".absolute.bottom-0").bounding_box()
        if footer_box:
            print(f"Footer detected at Y: {footer_box['y']}, Height: {footer_box['height']}")
            if footer_box['y'] + footer_box['height'] <= 844:
                print("PASS: Footer is within viewport.")
            else:
                print("FAIL: Footer might be off-screen.")
        else:
            print("FAIL: Footer not found.")

        browser.close()

if __name__ == "__main__":
    verify_layout()
