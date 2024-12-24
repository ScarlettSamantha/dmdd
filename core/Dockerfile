FROM python:3.12-slim

# Set the working directory
WORKDIR /app

# Copy the requirements file to the container
COPY requirements.txt ./

# Install Python dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Copy the application code to the container
COPY . ./

# Set the entry point for the container
CMD ["gunicorn", "-w", "4", "-b", "0.0.0.0:5000", "main:api_app"]