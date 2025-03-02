from datetime import datetime, timedelta

def esempi_date():
    # 1. CREAZIONE DATE
    data_esempio = datetime(2024, 3, 15, 14, 30, 0)
    print("\n=== Date specifiche ===")
    print(f"Data esempio: {data_esempio}")
    
    adesso = datetime.now()
    print(f"Adesso: {adesso}")

    # 2. FORMATTAZIONE
    print("\n=== Formati diversi ===")
    formati = {
        "Standard italiano": "%d/%m/%Y",
        "Con ore": "%d/%m/%Y %H:%M:%S",
        "Testuale breve": "%a %d %b %Y",
        "Testuale completo": "%A %d %B %Y",
        "Solo ora": "%H:%M:%S",
        "ISO": "%Y-%m-%d %H:%M:%S",
        "Personalizzato": "Sono le ore %H:%M del giorno %d/%m/%Y"
    }

    for nome, formato in formati.items():
        print(f"{nome}: {adesso.strftime(formato)}")

    # 3. CALCOLI CON LE DATE
    print("\n=== Calcoli temporali ===")
    intervalli = {
        "Tra 1 ora": timedelta(hours=1),
        "Domani": timedelta(days=1),
        "Tra una settimana": timedelta(weeks=1),
        "Tra 1 mese (approx)": timedelta(days=30),
        "Tra 1 anno (approx)": timedelta(days=365)
    }

    for nome, delta in intervalli.items():
        data_futura = adesso + delta
        print(f"{nome}: {data_futura.strftime('%d/%m/%Y %H:%M:%S')}")

    # 4. CONFRONTI TRA DATE
    print("\n=== Confronti ===")
    data1 = datetime(2024, 3, 1, 10, 0)
    data2 = datetime(2024, 3, 15, 15, 30)
    
    differenza = data2 - data1
    print(f"Differenza tra {data2.strftime('%d/%m/%Y')} e {data1.strftime('%d/%m/%Y')}: {differenza.days} giorni")
    print(f"Differenza in ore: {differenza.total_seconds() / 3600:.1f} ore")

    # 5. PARSING DI STRINGHE IN DATE
    print("\n=== Parsing di stringhe ===")
    stringa_data = "15/03/2024 14:30:00"
    data_parsata = datetime.strptime(stringa_data, "%d/%m/%Y %H:%M:%S")
    print(f"Stringa originale: {stringa_data}")
    print(f"Data parsata: {data_parsata}")
    print(f"Riformattata: {data_parsata.strftime('%A %d %B %Y, %H:%M')}")

if __name__ == "__main__":
    esempi_date()