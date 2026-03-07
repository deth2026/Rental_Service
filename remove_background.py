import requests
from rembg import remove
from PIL import Image
import io

# URL of the image
url = "https://assets.grok.com/users/3eaabd2d-e536-46ac-a895-d7d0a98c809b/generated/a293c403-a50c-4f3e-9f39-e2b50f077872/image.jpg"

# Download the image
print("Downloading image...")
response = requests.get(url)
img = Image.open(io.BytesIO(response.content))

# Remove background
print("Removing background...")
output = remove(img)

# Save the result
output_path = "c:/Users/SREYMOM.PO/Desktop/Rental_Service/frontend/src/assets/logo_no_background.png"
output.save(output_path)
print(f"Image saved to: {output_path}")

