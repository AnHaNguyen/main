import json
import sys
import getopt
import unicodedata
import re


def main():
	module_file, semester_file = load_args()
	mod_dict = simplify(module_file)
	mod_dict = include_semester_info(mod_dict, semester_file)
	create_json_file(mod_dict)
	print len(mod_dict)
	cr_file = file("corequisites.json", 'w')
	for key, value in mod_dict.iteritems():
		if "CR" in value:
			cr_file.write(key)
			cr_file.write("\n")
			cr_file.write(value["CR"].encode('ascii', 'ignore'))
			cr_file.write("\n\n")
	cr_file.close()


def load_args():
	""" Loads arguments into variables

	:return: module_file and semester_file variables
	"""
	module_file = semester_file = None

	try:
		opts, args = getopt.getopt(sys.argv[1:], 'm:s:')
	except getopt.GetoptError, err:
		usage()
		sys.exit(2)
	for o, a in opts:
		if o == '-m':
			module_file = a
		elif o == '-s':
			semester_file = a
		else:
			assert False, "unhandled option"
	if module_file is None:
		usage()
		sys.exit(2)
	return module_file, semester_file


def usage():
	"""Prints the proper format for calling this script."""
	print "usage: " + sys.argv[0] + " -m module_file"


def simplify(module_file):
	""" Removes redundant fields (such as CORS bidding stats and timetables) from the modules.json file, and also shortens
	the names of keys to reduce the file size. Returns a dictionary, that uses module codes as keys, of dictionaries
	containing other information about that module, which is elaborated below.

	Each module dictionary has the following information:
	- ModuleTitle (NM) = Name of this module.
	- ModuleCredit (MC) = No. of credits this module is worth.
	- ParsedPreclusion (PC) = List of modules that cannot be taken together with this module.
	- Prerequisite (PR) = Plaintext information detailing prerequisites for this module.
	- ParsedPrerequisite (PP) = List of modules that are prerequisites for this module.
	- Corequisite (CR) = Plaintext information detailing corequisites for this module.

	:param module_file: File containing module details, kindly provided by NUSMods' API.
	:return: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	"""
	with open(module_file) as json_file:
		modules = json.load(json_file)
		mod_dict = {}

		for module in modules:
			keys = module.keys()
			for key in keys:
				if key not in {"ModuleCode", "ModuleTitle", "ModuleCredit", "ParsedPreclusion",
							"Prerequisite", "ParsedPrerequisite", "Corequisite"}:
					module.pop(key, None)

			# Shorten name of keys
			module["NM"] = module.pop("ModuleTitle")
			module["MC"] = module.pop("ModuleCredit")
			temp = module.pop("ParsedPreclusion", None)
			if temp is not None:
				module["PC"] = temp
			temp = module.pop("Prerequisite", None)
			if temp is not None:
				module["PR"] = temp
			temp = module.pop("ParsedPrerequisite", None)
			if temp is not None:
				module["PP"] = temp
			temp = module.pop("Corequisite", None)
			if temp is not None:
				module["CR"] = temp

			mod_dict[module.pop("ModuleCode")] = module

		return mod_dict


def include_semester_info(mod_dict, semester_file):
	"""Extracts info about which semesters modules are offered in, and includes this info in the dictionary of modules

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	:param semester_file: File containing info about which semesters modules are offered in, kindly provided by NUSMods' API.
	:return: mod_dict updated with info about which semesters modules are offered in
	"""
	with open(semester_file) as json_file:
		mod_sems = json.load(json_file)

		for module in mod_sems:
			mod_dict[str(module["ModuleCode"])]["SM"] = module["Semesters"]

		return mod_dict


def create_json_file(mod_dict):
	"""Writes the dictionary (that uses module codes as keys) of dictionaries (containing certain module information),
	to a JSON file on disk.

	:param mod_dict: A dictionary (that uses module codes as keys) of dictionaries (containing certain module information).
	:return:
	"""
	module_file = file("simplified.json", 'w')
	json.dump(mod_dict, module_file)
	module_file.close()


if __name__ == "__main__":
	main()
