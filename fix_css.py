with open('c:/Users/SREYMOM.PO/Desktop/Rental_Service/frontend/src/assets/HomeView.css', 'r') as f:
    content = f.read()

# Split into lines for easier processing
lines = content.split('\n')
result = []
i = 0

while i < len(lines):
    line = lines[i]
    if '<<<<<<<' in line:
        # Found start of conflict
        # Skip this line
        i += 1
        # Skip until we find =======
        while i < len(lines) and '=======' not in lines[i]:
            i += 1
        # Skip the ======= line too
        if i < len(lines):
            i += 1
        # Now we're at the "theirs" version - keep these lines
        while i < len(lines) and '>>>>>>>' not in lines[i]:
            result.append(lines[i])
            i += 1
        # Skip the >>>>>>> line
        if i < len(lines):
            i += 1
    else:
        result.append(line)
        i += 1

# Join and write back
with open('c:/Users/SREYMOM.PO/Desktop/Rental_Service/frontend/src/assets/HomeView.css', 'w') as f:
    f.write('\n'.join(result))

print('Fixed')
