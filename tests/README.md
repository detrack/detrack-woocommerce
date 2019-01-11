# Selenium test for WooCommerce plugin

An extremely simplified test to ensure checking out an order in WooComerce will produce a Job in Detrack.

## Requires

- **A desktop environment that can feed Chrome enough RAM** (I don't have time to get cancer from setting up selenium docker images)
- Python3
- An existing default WooCommerce setup
- A Detrack account

## Setup

Clone the repository, `cd` into this directory (`tests`)

```bash
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

## How to use

- Export the required environment variables:
  - `WP_ADMIN_PATH` – URL to your `wp-admin` page
  - `WP_ADMIN_USERNAME` - admin username
  - `WP_ADMIN_PASSWORD` - admin password
  - `DETRACK_DASHBOARD_PATH` – should always be `https://app.detrack.com/dashboard/#/login`
  - `DETRACK_DASHBOARD_USERNAME` – your Detrack username
  - `DETRACK_DASHBOARD_PASSWORD` – your Detrack password
- Run the test suite: `python test.py`
