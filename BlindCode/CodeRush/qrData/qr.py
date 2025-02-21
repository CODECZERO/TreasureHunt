import qrcode
import uuid
import os

# Take user input for QR code data
data = input("Enter the text or URL for the QR code: ")

# Generate a unique filename using UUID
filename = f"qr_{uuid.uuid4().hex}.png"

# Create a QR Code instance
qr = qrcode.QRCode(
    version=1,  # Size of the QR code (1-40, higher = more complex)
    error_correction=qrcode.constants.ERROR_CORRECT_L,  # Error correction level
    box_size=10,  # Size of each box in the QR grid
    border=4,  # Border thickness
)

# Add data to the QR code
qr.add_data(data)
qr.make(fit=True)

# Generate and save the QR code as an image
qr_img = qr.make_image(fill="black", back_color="white")

# Save the image with the unique filename
save_path = os.path.join(os.getcwd(), filename)
qr_img.save(save_path)

print(f"QR Code generated and saved as '{filename}'")
