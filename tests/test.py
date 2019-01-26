from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import chromedriver_binary # Adds chromedriver binary to path
import os
import random

#function to bring driver window to front
def btf(driver):
    driver.execute_script("alert()")
    alert = driver.switch_to.alert
    alert.dismiss()

# Retrieve required environment variables
wp_admin_path = os.environ.get("WP_ADMIN_PATH")
wp_admin_username = os.environ.get("WP_ADMIN_USERNAME")
wp_admin_password = os.environ.get("WP_ADMIN_PASSWORD")
detrack_dashboard_path = os.environ.get("DETRACK_DASHBOARD_PATH");
detrack_dashboard_username = os.environ.get("DETRACK_DASHBOARD_USERNAME");
detrack_dashboard_password = os.environ.get("DETRACK_DASHBOARD_PASSWORD");

# Login to WooCommerce
driver = webdriver.Chrome()
driver.delete_all_cookies()
driver.get(wp_admin_path);
assert "Log In" in driver.title
elem = driver.find_element_by_id("user_login")
elem.clear()
elem.send_keys(wp_admin_username)
elem = driver.find_element_by_id("user_pass")
elem.clear()
elem.send_keys(wp_admin_password)
elem = driver.find_element_by_id("wp-submit")
elem.click()
assert "Dashboard" in driver.title

# Open new driver instance, login to detrack dashboard
detrack_driver = webdriver.Chrome()
detrack_driver.implicitly_wait(5) # because detrack dashboard is built on angular, need wait for elements to render
detrack_driver.get(detrack_dashboard_path)
assert "Detrack" in detrack_driver.title

elem = detrack_driver.find_element_by_css_selector("#login-form > fieldset > section:nth-child(1) > label.input > input")
elem.clear()
elem.send_keys(detrack_dashboard_username)
elem = detrack_driver.find_element_by_css_selector("#login-form > fieldset > section:nth-child(2) > label.input > input")
elem.clear()
elem.send_keys(detrack_dashboard_password)
elem = detrack_driver.find_element_by_css_selector("#login-form > footer > button")
elem.click()
assert "Login" not in detrack_driver.current_url

elem = detrack_driver.find_element_by_css_selector("#left-panel > nav > ul > li:nth-child(3)")
elem.click()
elem = detrack_driver.find_element_by_css_selector("#left-panel > nav > ul > li:nth-child(3) > ul > li:nth-child(1)")
elem.click()

# Purchase something in WooCommerce store
btf(driver)
elem = driver.find_element_by_id("wp-admin-bar-site-name")
hov = ActionChains(driver).move_to_element(elem)
hov.perform()
driver.implicitly_wait(5)
elem = driver.find_element_by_css_selector("#wp-admin-bar-view-store > a")
elem.click()
assert "Products" in driver.title

products = driver.find_elements_by_css_selector("a.add_to_cart_button")
elem = random.choice(products)
driver.execute_script("window.scroll(0,arguments[0].offsetTop - document.getElementById('wpadminbar').height)", elem)
elem.click()
elem = driver.find_element_by_css_selector("#site-navigation > div:nth-child(2) > ul > li.page_item.page-item-6 > a")
elem.click()
assert "Cart" in driver.title

elem = driver.find_element_by_css_selector("#post-6 > div > div > div > div > div > a")
elem.click()
assert "Checkout" in driver.title

# Populate test order data
order = {}
order["first_name"] = "Selenium"
order["last_name"] = "Webdriver"
order["company"] = "Detrack"
order["address_1"] = "8 Somapah Road"
order["address_2"] = "Building 1 Level 3"
order["city"] = "Singapore"
order["postcode"] = "487372"
order["phone"] = "68440509"
order["email"] = "detrack@mailinator.com"

elem = driver.find_element_by_id("billing_first_name")
elem.clear()
elem.send_keys(order["first_name"])
elem = driver.find_element_by_id("billing_last_name")
elem.clear()
elem.send_keys(order["last_name"])
elem = driver.find_element_by_id("billing_company")
elem.clear()
elem.send_keys(order["company"])
elem = driver.find_element_by_css_selector("#billing_country_field > span > span > span.selection > span")
elem.click()
elems = driver.find_elements_by_css_selector("li.select2-results__option")
elem = next(elem for elem in elems if "Singapore" in elem.text)
elem.click()
elem = driver.find_element_by_id("billing_address_1")
elem.clear()
elem.send_keys(order["address_1"])
elem = driver.find_element_by_id("billing_address_2")
elem.clear()
elem.send_keys(order["address_2"])
# elem = driver.find_element_by_id("billing_city")
# elem.clear()
# elem.send_keys(order["city"])
elem = driver.find_element_by_id("billing_postcode")
elem.clear()
elem.send_keys(order["postcode"])
elem = driver.find_element_by_id("billing_phone")
elem.clear()
elem.send_keys(order["phone"])
elem = driver.find_element_by_id("billing_email")
elem.clear()
elem.send_keys(order["email"])
elem = driver.find_element_by_id("ship-to-different-address-checkbox")
elem.click()
wait = WebDriverWait(driver, 10)
elem = wait.until(EC.invisibility_of_element((By.CLASS_NAME,'blockOverlay')))
elem = wait.until(EC.element_to_be_clickable((By.ID, 'place_order')))
elem.click()
wait.until(EC.url_changes(driver.current_url))
elem = driver.find_element_by_css_selector("#post-7 > header > h1")
assert "Order received" in elem.text

# Gather order review data
elem = driver.find_element_by_css_selector("#post-7 > div > div > div > ul > li.woocommerce-order-overview__order.order > strong")
order["do"] = elem.text
btf(detrack_driver)
elem = detrack_driver.find_element_by_css_selector("#left-panel > nav > ul > li:nth-child(3)")
hov = ActionChains(detrack_driver).move_to_element(elem)
hov.perform()
elem = detrack_driver.find_element_by_css_selector("#left-panel > nav > ul > li:nth-child(3) > ul > li:nth-child(3) > a")
elem.click()
wait = WebDriverWait(detrack_driver, 10)
wait.until(EC.invisibility_of_element((By.CLASS_NAME,"router-animation-loader")))
wait.until(EC.invisibility_of_element((By.CLASS_NAME,"loading-bar")))
elem = detrack_driver.find_element_by_id("AllJobsDo")
elem.clear()
elem.send_keys(order["do"])
elem = detrack_driver.find_element_by_css_selector("#searchJobs-jobs-widget > div > form > fieldset:nth-child(1) > div.text-center > button.btn.btn-primary.ng-binding")
elem.click()
wait.until(EC.invisibility_of_element((By.CLASS_NAME,"loading-bar")))
elem = next(elem for elem in detrack_driver.find_elements_by_css_selector("#dtAllJobs td:nth-child(4)") if elem.text==order["do"]).find_element_by_xpath('..')
detrack_driver.execute_script("arguments[0].scrollIntoView();", elem)
assert order["do"] in elem.find_element_by_css_selector("td:nth-child(4)").text
assert order["address_1"] in elem.find_element_by_css_selector("td:nth-child(8)").text
assert order["address_2"] in elem.find_element_by_css_selector("td:nth-child(8)").text
assert order["city"] in elem.find_element_by_css_selector("td:nth-child(8)").text
assert order["postcode"] in elem.find_element_by_css_selector("td:nth-child(8)").text

# Pause before closing
input("\nAll tests passed\nPress any key to continue")
driver.close()
detrack_driver.close()
