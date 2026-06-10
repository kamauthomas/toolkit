FROM python:3.12-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY . .

RUN mkdir -p instance generated_reports logs

EXPOSE 5055

CMD ["gunicorn", "-w", "2", "-b", "0.0.0.0:5055", "--access-logfile", "-", "--error-logfile", "-", "wsgi:application"]