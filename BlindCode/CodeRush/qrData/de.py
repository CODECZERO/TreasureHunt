
from PIL import Image, ImageDraw
import numpy as np
import math

### ---- ENCODER ---- ###
def text_to_binary(text):
    """Convert text to an 8-bit binary string"""
    return ''.join(format(ord(char), '08b') for char in text)

def encode_text_to_qr_style_image(text, block_size=10, border=4):
    """Encodes text into a QR-style black & white image"""
    binary_data = text_to_binary(text)
    length = len(binary_data)

    # Find nearest square size
    size = math.ceil(math.sqrt(length))

    # Image size with border
    img_size = size + 2 * border

    # Create a blank white image
    img = Image.new("L", (img_size * block_size, img_size * block_size), color=255)
    draw = ImageDraw.Draw(img)

    # Fill pixels as QR-like blocks
    for i, bit in enumerate(binary_data):
        row, col = divmod(i, size)
        x, y = (col + border) * block_size, (row + border) * block_size
        if bit == '1':  # Black block for '1'
            draw.rectangle([x, y, x + block_size - 1, y + block_size - 1], fill=0)

    # Save QR-style image
    img.save("qr_style_encoded.png")
    print(f"Encoded QR-like image saved as 'qr_style_encoded.png' (Size: {img_size * block_size}x{img_size * block_size})")

### ---- DECODER ---- ###
def decode_qr_style_image(image_path, block_size=10, border=4):
    """Decodes a QR-style binary image back to text"""
    img = Image.open(image_path).convert("L")  # Open as grayscale
    img_array = np.array(img)

    # Determine actual size without border
    img_size = img_array.shape[0] // block_size
    size = img_size - 2 * border

    # Extract binary from the QR pattern
    binary_str = ''
    for row in range(border, size + border):
        for col in range(border, size + border):
            pixel = img_array[row * block_size, col * block_size]
            binary_str += '1' if pixel < 128 else '0'  # Threshold for black/white

    # Convert binary to text
    decoded_text = ''.join(chr(int(binary_str[i:i+8], 2)) for i in range(0, len(binary_str), 8))
    return decoded_text.strip('\x00')

### ---- TEST RUN ---- ###
if __name__ == "__main__":
    input_text = '''' I am a place where challenges await,
A door locked tight, but not by fate.
The numbers stand where wisdom reigns,
A path untold, yet nothing strange.
A port of calls, where seekers find,
The key within, if you're inclined.
Look beyond, and you shall see,
A hidden way to set you free.TDJOMFpreHZaMmx1TG5Cb2NBbz0KCg=='''
    encode_text_to_qr_style_image(input_text)

    decoded_text = decode_qr_style_image("qr_style_encoded.png")
    print(f"Decoded Text: {decoded_text}")
