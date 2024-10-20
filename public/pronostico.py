import pandas as pd
from prophet import Prophet
import json

# Simular datos de ingresos o cargar desde la base de datos
df = pd.read_csv('resources/ingresos.csv')  # Simulación o consulta a la base de datos
df = df.rename(columns={'fecha': 'ds', 'monto': 'y'})

# Crear y entrenar el modelo Prophet
model_prophet = Prophet()
model_prophet.fit(df)

# Pronóstico para los próximos 180 días
future = model_prophet.make_future_dataframe(periods=180)
forecast = model_prophet.predict(future)

# Convertir a JSON
result = forecast[['ds', 'yhat', 'yhat_lower', 'yhat_upper']].tail(180).to_json(orient='records')

# Imprimir el resultado para que Laravel pueda leerlo
print(result)
