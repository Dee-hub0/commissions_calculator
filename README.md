```markdown
# Commission Calculator 🛫

A simple PHP application to calculate commissions from a CSV file, with an optional integration to exchange rates.

---

## 🛠️ Prerequisites

Before running the project, make sure your environment meets the following requirements:

- **PHP** 8.2.0 or higher
- **Symfony** 7.1.5

---

## 📅 Project Duration

- **Time taken**: 13 hours

---

## 🚀 Getting Started

### 1. Clone the Project

Clone this repository to your local machine using:

```bash
git clone https://github.com/your-repository-url
```

### 2. Start the Local Server

Navigate to the project folder and start the Symfony local server:

```bash
cd your-project-directory
symfony serve
```

---

## ⚙️ Configuration (Optional)

If you need to configure the exchange rate API, follow the steps below:
Otherwise, a default exchange rate data array is provided, which will yield the same results for the input shown in the 'Usage' section below."

1. Create or edit the `.env` or `.env.local` file.
2. Add the following environment variables:

```env
###> Exchange Rate API ###
RATE_API_URL=https://api.exchangeratesapi.io/latest
RATE_API_KEY=your-api-key
###< Exchange Rate API ###
```

Replace `your-api-key` with your actual API key for the exchange rate service.

---

## 💻 Usage

### Running Commission Calculation

To calculate commissions from a CSV file, follow these steps:

1. Navigate to the project directory if you haven't already.
2. Run the following command:

```bash
php bin/console app:calculate-commissions
```

- **CSV Input**: By default, a sample CSV file is included in the project. You can specify your own CSV file by providing the path in the command.

### Example Input:

Here’s an example of the format expected in the CSV file:

```csv
2014-12-31,4,private,withdraw,1200.00,EUR  
2015-01-01,4,private,withdraw,1000.00,EUR  
2016-01-05,4,private,withdraw,1000.00,EUR  
2016-01-05,1,private,deposit,200.00,EUR  
2016-01-06,2,business,withdraw,300.00,EUR  
2016-01-06,1,private,withdraw,30000,JPY  
2016-01-07,1,private,withdraw,1000.00,EUR  
2016-01-07,1,private,withdraw,100.00,USD  
2016-01-10,1,private,withdraw,100.00,EUR  
2016-01-10,2,business,deposit,10000.00,EUR  
2016-01-10,3,private,withdraw,1000.00,EUR  
2016-02-15,1,private,withdraw,300.00,EUR  
2016-02-19,5,private,withdraw,3000000,JPY  
```

### Example Output:

The output will show the calculated commission for each transaction in the CSV file:

```txt
0.60  
3.00  
0.00  
0.06  
1.50  
0  
0.70  
0.30  
0.30  
3.00  
0.00  
0.00  
8612
```

---

## 🧪 Running Tests

You can run the unit tests for the application by executing the following command:

```bash
php bin/phpunit
```

This will run all the available tests and show the results in your terminal.
