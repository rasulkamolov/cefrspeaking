from playwright.sync_api import sync_playwright

def verify_admin_layout():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        # 1080p Desktop Viewport
        context = browser.new_context(viewport={'width': 1920, 'height': 1080})
        page = context.new_page()

        print("Navigating to Login...")
        page.goto("http://127.0.0.1:8080/pages/login.php")

        # Login as Admin
        page.fill("input[name='email']", "admin@jules.com")
        page.fill("input[name='password']", "admin123")
        page.click("button[type='submit']")
        page.wait_for_url("**/admin/dashboard.php")
        print("Admin Logged In.")

        # Go to Manage Tests
        page.click("text=Manage Tests")
        page.wait_for_url("**/admin/manage_tests.php")

        # Verify Scrolling Container exists
        # We look for main.flex-1.overflow-y-auto
        scroll_container = page.locator("main.flex-1.overflow-y-auto")
        if scroll_container.count() > 0:
            print("PASS: Manage Tests has scrollable container.")
        else:
            print("FAIL: Manage Tests scroll container missing.")

        page.screenshot(path="admin_manage_tests.png")

        # Go to Edit Test (First one)
        page.locator("text=Edit").first.click()
        page.wait_for_url("**/admin/edit_test.php*")

        # Verify Scrolling Container exists
        scroll_container = page.locator("main.flex-1.overflow-y-auto")
        if scroll_container.count() > 0:
            print("PASS: Edit Test has scrollable container.")
        else:
            print("FAIL: Edit Test scroll container missing.")

        page.screenshot(path="admin_edit_test.png")

        browser.close()

if __name__ == "__main__":
    verify_admin_layout()
