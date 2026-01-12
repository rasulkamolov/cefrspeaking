
from playwright.sync_api import sync_playwright

def verify_student_flow():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context()
        page = context.new_page()

        try:
            # 1. Login as Student
            print("Navigating to login page...")
            page.goto("http://localhost:8000/login.php")
            page.fill("input[name='email']", "student@jules.com")
            page.fill("input[name='password']", "student123")
            page.click("button[type='submit']")

            # 2. Verify Dashboard
            print("Verifying dashboard...")
            page.wait_for_selector("text=Jules Exam Room")
            page.screenshot(path="verification/student_dashboard.png")
            print("Dashboard verified.")

            # 3. Start Exam
            print("Starting exam...")
            page.click("text=Start Full Exam")

            # 4. Verify Part 1.1 Intro
            print("Verifying Part 1.1...")
            page.wait_for_selector("text=Part 1.1")
            page.screenshot(path="verification/exam_part_1_1.png")

            # Note: We can't easily record audio in headless, but we can verify the UI elements

        except Exception as e:
            print(f"Error: {e}")
            page.screenshot(path="verification/error.png")
        finally:
            browser.close()

if __name__ == "__main__":
    verify_student_flow()
